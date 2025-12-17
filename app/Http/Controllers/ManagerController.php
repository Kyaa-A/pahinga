<?php

namespace App\Http\Controllers;

use App\Enums\LeaveStatus;
use App\Enums\UserRole;
use App\Models\CompanyHoliday;
use App\Models\LeaveRequest;
use App\Models\ManagerDelegation;
use App\Models\User;
use App\Notifications\LeaveRequestApproved;
use App\Notifications\LeaveRequestDenied;
use App\Services\ConflictDetectionService;
use App\Services\LeaveBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function __construct(
        protected ConflictDetectionService $conflictService,
        protected LeaveBalanceService $leaveBalanceService
    ) {
        //
    }

    /**
     * Display the manager dashboard.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        // Load direct reports count once and cache it
        $user->loadCount('directReports');

        // Get statistics
        $pendingCount = LeaveRequest::forManager($user->id)->pending()->count();
        $approvedThisMonth = LeaveRequest::forManager($user->id)
            ->approved()
            ->whereMonth('reviewed_at', now()->month)
            ->whereYear('reviewed_at', now()->year)
            ->count();

        // Get recent pending requests
        $recentRequests = LeaveRequest::with(['user'])
            ->forManager($user->id)
            ->pending()
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->get();

        // Get upcoming approved leaves
        $upcomingLeaves = LeaveRequest::with(['user'])
            ->forManager($user->id)
            ->approved()
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Get conflict summary
        $conflictSummary = $this->conflictService->getConflictSummary($user->id);

        // Get current team availability (next 30 days)
        $currentAvailability = $this->conflictService->calculateTeamAvailability(
            $user->id,
            now(),
            now()->addDays(30)
        );

        return view('manager.dashboard', [
            'pendingCount' => $pendingCount,
            'approvedThisMonth' => $approvedThisMonth,
            'teamSize' => $user->direct_reports_count,
            'recentRequests' => $recentRequests,
            'upcomingLeaves' => $upcomingLeaves,
            'conflictSummary' => $conflictSummary,
            'currentAvailability' => $currentAvailability,
        ]);
    }

    /**
     * Display all pending leave requests from the manager's team.
     */
    public function pendingRequests(Request $request): View
    {
        $user = $request->user();

        $pendingRequests = LeaveRequest::with(['user'])
            ->forManager($user->id)
            ->pending()
            ->orderByDesc('submitted_at')
            ->paginate(20);

        // Check for conflicts for each request using the service
        foreach ($pendingRequests as $leaveRequest) {
            $leaveRequest->conflicts = $this->conflictService->checkConflicts($leaveRequest, $user->id);
        }

        return view('manager.pending-requests', [
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * Show a specific leave request for review.
     */
    public function showRequest(Request $request, LeaveRequest $leaveRequest): View
    {
        // Ensure the request belongs to this manager's team
        if ($leaveRequest->manager_id !== auth()->id()) {
            abort(403, 'You can only review requests from your team.');
        }

        $leaveRequest->load(['user', 'manager', 'history.performedBy']);

        // Check for conflicts using the service
        $conflicts = $this->conflictService->checkConflicts($leaveRequest, auth()->id());

        return view('manager.review-request', [
            'leaveRequest' => $leaveRequest,
            'conflicts' => $conflicts,
        ]);
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        // Ensure the request belongs to this manager's team
        if ($leaveRequest->manager_id !== $request->user()->id) {
            abort(403, 'You can only approve requests from your team.');
        }

        if (! $leaveRequest->isPending()) {
            return back()->withErrors(['status' => 'Only pending requests can be approved.']);
        }

        $request->validate([
            'manager_notes' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => LeaveStatus::Approved,
            'manager_notes' => $request->manager_notes,
            'reviewed_at' => now(),
        ]);

        // Deduct from balance (move from pending to used)
        try {
            $this->leaveBalanceService->deductFromBalance($leaveRequest->id);
        } catch (\Exception $e) {
            // If balance deduction fails, revert the approval
            $leaveRequest->update([
                'status' => LeaveStatus::Pending,
                'manager_notes' => null,
                'reviewed_at' => null,
            ]);

            return back()->withErrors([
                'balance' => 'Failed to deduct balance: '.$e->getMessage(),
            ]);
        }

        $leaveRequest->recordHistory(
            'approved',
            $request->user()->id,
            'Approved by manager'.($request->manager_notes ? ': '.$request->manager_notes : '')
        );

        // Send notification to employee (wrapped in try-catch to prevent blocking on mail errors)
        try {
            $leaveRequest->user->notify(new LeaveRequestApproved($leaveRequest));
        } catch (\Exception $e) {
            logger()->warning('Failed to send approval notification', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('manager.pending-requests')
            ->with('success', 'Leave request approved successfully!');
    }

    /**
     * Deny a leave request.
     */
    public function deny(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        // Ensure the request belongs to this manager's team
        if ($leaveRequest->manager_id !== $request->user()->id) {
            abort(403, 'You can only deny requests from your team.');
        }

        if (! $leaveRequest->isPending()) {
            return back()->withErrors(['status' => 'Only pending requests can be denied.']);
        }

        $request->validate([
            'manager_notes' => 'required|string|max:1000',
        ], [
            'manager_notes.required' => 'Please provide a reason for denying this request.',
        ]);

        $leaveRequest->update([
            'status' => LeaveStatus::Denied,
            'manager_notes' => $request->manager_notes,
            'reviewed_at' => now(),
        ]);

        // Restore balance (move from pending back to available)
        try {
            $this->leaveBalanceService->restoreToBalance($leaveRequest->id);
        } catch (\Exception $e) {
            // Log the error but don't fail the denial
            logger()->error('Failed to restore balance on denial', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        $leaveRequest->recordHistory(
            'denied',
            $request->user()->id,
            'Denied by manager: '.$request->manager_notes
        );

        // Send notification to employee (wrapped in try-catch to prevent blocking on mail errors)
        try {
            $leaveRequest->user->notify(new LeaveRequestDenied($leaveRequest));
        } catch (\Exception $e) {
            logger()->warning('Failed to send denial notification', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('manager.pending-requests')
            ->with('success', 'Leave request denied.');
    }

    /**
     * Display team calendar with all approved leaves.
     */
    public function teamCalendar(Request $request): View
    {
        $user = $request->user();

        // Load direct reports count once
        $user->loadCount('directReports');

        // Get month and year from request, default to current
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all approved and pending leaves for the month
        $leaves = LeaveRequest::with(['user'])
            ->forManager($user->id)
            ->whereIn('status', [LeaveStatus::Approved, LeaveStatus::Pending])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->orderBy('start_date')
            ->get();

        // Get daily availability breakdown
        $dailyAvailability = $this->conflictService->getDailyAvailability($user->id, $startDate);

        // Get month availability summary
        $monthAvailability = $this->conflictService->calculateTeamAvailability(
            $user->id,
            $startDate,
            $endDate
        );

        // Get holidays for the month
        $holidays = CompanyHoliday::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($holiday) => $holiday->date->format('Y-m-d'));

        return view('manager.team-calendar', [
            'leaves' => $leaves,
            'currentMonth' => $startDate,
            'teamSize' => $user->direct_reports_count,
            'dailyAvailability' => $dailyAvailability,
            'monthAvailability' => $monthAvailability,
            'holidays' => $holidays,
        ]);
    }

    /**
     * Display team status showing who's on leave and who's available.
     */
    public function teamStatus(Request $request): View
    {
        $user = $request->user();

        // Get date from request, default to today
        $date = $request->filled('date') ? now()->parse($request->date) : now();
        $dateFormatted = $date->format('Y-m-d');
        $weekLaterFormatted = $date->copy()->addDays(7)->format('Y-m-d');

        // Get all team members with their leave requests in ONE query using eager loading
        $teamMembers = $user->directReports()
            ->with([
                'leaveRequests' => function ($query) use ($dateFormatted, $weekLaterFormatted) {
                    $query->where('status', LeaveStatus::Approved)
                        ->where(function ($q) use ($dateFormatted, $weekLaterFormatted) {
                            // Current leave: overlaps with today
                            $q->where(function ($subQ) use ($dateFormatted) {
                                $subQ->where('start_date', '<=', $dateFormatted)
                                    ->where('end_date', '>=', $dateFormatted);
                            })
                            // OR upcoming leave: starts within next 7 days
                                ->orWhere(function ($subQ) use ($dateFormatted, $weekLaterFormatted) {
                                    $subQ->where('start_date', '>', $dateFormatted)
                                        ->where('start_date', '<=', $weekLaterFormatted);
                                });
                        })
                        ->orderBy('start_date');
                },
            ])
            ->get();

        // Build team status data
        $teamStatus = [];
        $onLeaveCount = 0;
        $availableCount = 0;

        foreach ($teamMembers as $member) {
            // Separate current and upcoming leaves from the pre-loaded data
            $currentLeave = null;
            $upcomingLeave = null;

            foreach ($member->leaveRequests as $leave) {
                // Check if this leave overlaps with the selected date
                if ($leave->start_date <= $dateFormatted && $leave->end_date >= $dateFormatted) {
                    $currentLeave = $leave;
                }
                // Check if this is an upcoming leave
                elseif ($leave->start_date > $dateFormatted && $leave->start_date <= $weekLaterFormatted) {
                    if (! $upcomingLeave) {
                        $upcomingLeave = $leave;
                    }
                }
            }

            $status = 'available';
            if ($currentLeave) {
                $status = 'on_leave';
                $onLeaveCount++;
            } else {
                $availableCount++;
            }

            $teamStatus[] = [
                'member' => $member,
                'status' => $status,
                'current_leave' => $currentLeave,
                'upcoming_leave' => $upcomingLeave,
            ];
        }

        // Sort: on leave first, then by name
        usort($teamStatus, function ($a, $b) {
            if ($a['status'] === 'on_leave' && $b['status'] !== 'on_leave') {
                return -1;
            }
            if ($a['status'] !== 'on_leave' && $b['status'] === 'on_leave') {
                return 1;
            }

            return strcmp($a['member']->name, $b['member']->name);
        });

        // Calculate availability percentage
        $totalTeam = $teamMembers->count();
        $availabilityPercentage = $totalTeam > 0 ? round(($availableCount / $totalTeam) * 100, 1) : 100;

        return view('manager.team-status', [
            'teamStatus' => $teamStatus,
            'selectedDate' => $date,
            'onLeaveCount' => $onLeaveCount,
            'availableCount' => $availableCount,
            'totalTeam' => $totalTeam,
            'availabilityPercentage' => $availabilityPercentage,
        ]);
    }

    /**
     * Display delegation management page.
     */
    public function delegations(Request $request): View
    {
        $user = $request->user();

        // Get all delegations for this manager
        $delegations = ManagerDelegation::forManager($user->id)
            ->with(['delegate'])
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        // Get all other managers who can be delegates
        $availableDelegates = User::where('role', UserRole::Manager)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('manager.delegations', [
            'delegations' => $delegations,
            'availableDelegates' => $availableDelegates,
        ]);
    }

    /**
     * Store a new delegation.
     */
    public function storeDelegation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegate_manager_id' => ['required', 'exists:users,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ], [
            'delegate_manager_id.required' => 'Please select a delegate manager.',
            'delegate_manager_id.exists' => 'Selected delegate manager does not exist.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after the start date.',
        ]);

        // Verify the delegate is actually a manager
        $delegate = User::findOrFail($validated['delegate_manager_id']);
        if (! $delegate->isManager()) {
            return back()->withErrors(['delegate_manager_id' => 'Selected user is not a manager.']);
        }

        // Verify not delegating to self
        if ($delegate->id === $request->user()->id) {
            return back()->withErrors(['delegate_manager_id' => 'You cannot delegate to yourself.']);
        }

        // Check for overlapping delegations
        $overlapping = ManagerDelegation::forManager($request->user()->id)
            ->whereRaw('is_active = true')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($overlapping) {
            return back()->withErrors(['start_date' => 'A delegation already exists for this date range.']);
        }

        // Create the delegation
        ManagerDelegation::create([
            'manager_id' => $request->user()->id,
            'delegate_manager_id' => $validated['delegate_manager_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => true,
        ]);

        return redirect()->route('manager.delegations')->with('success', 'Delegation created successfully.');
    }

    /**
     * Deactivate a delegation.
     */
    public function deactivateDelegation(Request $request, ManagerDelegation $delegation): RedirectResponse
    {
        // Verify the delegation belongs to this manager
        if ($delegation->manager_id !== $request->user()->id) {
            abort(403, 'Unauthorized to deactivate this delegation.');
        }

        $delegation->update(['is_active' => false]);

        return redirect()->route('manager.delegations')->with('success', 'Delegation deactivated successfully.');
    }

    /**
     * Delete a delegation.
     */
    public function destroyDelegation(Request $request, ManagerDelegation $delegation): RedirectResponse
    {
        // Verify the delegation belongs to this manager
        if ($delegation->manager_id !== $request->user()->id) {
            abort(403, 'Unauthorized to delete this delegation.');
        }

        $delegation->delete();

        return redirect()->route('manager.delegations')->with('success', 'Delegation deleted successfully.');
    }
}
