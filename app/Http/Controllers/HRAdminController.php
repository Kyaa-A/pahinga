<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\CompanyHoliday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\ManagerDelegation;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HRAdminController extends Controller
{
    public function __construct(
        protected LeaveBalanceService $leaveBalanceService
    ) {
        //
    }

    /**
     * Display the HR Admin dashboard with system-wide statistics.
     */
    public function dashboard(Request $request): View
    {

        // Get system-wide statistics
        $totalEmployees = User::where('role', UserRole::Employee)->count();
        $totalManagers = User::where('role', UserRole::Manager)->count();
        $totalUsers = $totalEmployees + $totalManagers;

        $pendingRequests = LeaveRequest::pending()->count();
        $approvedThisMonth = LeaveRequest::approved()
            ->whereMonth('reviewed_at', now()->month)
            ->whereYear('reviewed_at', now()->year)
            ->count();

        $currentlyOnLeave = LeaveRequest::approved()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->count();

        // Get leave type breakdown for this year
        $leaveTypeBreakdown = LeaveRequest::approved()
            ->whereYear('start_date', now()->year)
            ->select('leave_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_days) as total_days'))
            ->groupBy('leave_type')
            ->get();

        // Get upcoming holidays
        $upcomingHolidays = CompanyHoliday::where('date', '>=', now())
            ->orderBy('date')
            ->limit(5)
            ->get();

        // Get recent leave requests (last 10)
        $recentRequests = LeaveRequest::with(['user', 'manager'])
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        // Get balance summary by leave type
        $balanceSummary = LeaveBalance::select('leave_type', DB::raw('SUM(available) as total_available'), DB::raw('SUM(used) as total_used'), DB::raw('SUM(pending) as total_pending'))
            ->groupBy('leave_type')
            ->get();

        return view('hr-admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalEmployees' => $totalEmployees,
            'totalManagers' => $totalManagers,
            'pendingRequests' => $pendingRequests,
            'approvedThisMonth' => $approvedThisMonth,
            'currentlyOnLeave' => $currentlyOnLeave,
            'leaveTypeBreakdown' => $leaveTypeBreakdown,
            'upcomingHolidays' => $upcomingHolidays,
            'recentRequests' => $recentRequests,
            'balanceSummary' => $balanceSummary,
        ]);
    }

    /**
     * Display all users with their leave balances.
     */
    public function users(Request $request): View
    {

        $users = User::with(['leaveBalances', 'manager'])
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->when($request->filled('department'), function ($query) use ($request) {
                $query->where('department', $request->department);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('email', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->whereRaw('is_active = true');
                } elseif ($request->status === 'inactive') {
                    $query->whereRaw('is_active = false');
                }
            })
            ->orderBy('name')
            ->paginate(20);

        $departments = User::select('department')->distinct()->whereNotNull('department')->pluck('department');

        return view('hr-admin.users.index', [
            'users' => $users,
            'departments' => $departments,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function createUser(): View
    {
        $managers = User::where('role', UserRole::Manager)->orderBy('name')->get();
        $departments = User::select('department')->distinct()->whereNotNull('department')->pluck('department');

        return view('hr-admin.users.create', [
            'managers' => $managers,
            'departments' => $departments,
            'roles' => UserRole::toArray(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function storeUser(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::enum(UserRole::class)],
            'department' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'department' => $validated['department'] ?? null,
                'manager_id' => $validated['manager_id'] ?? null,
            ]);

            // Initialize leave balances for employees
            if ($user->role === UserRole::Employee) {
                $this->leaveBalanceService->initializeBalances($user->id);
            }

            DB::commit();

            return redirect()
                ->route('hr-admin.users')
                ->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create user: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a user.
     */
    public function editUser(User $user): View
    {
        $managers = User::where('role', UserRole::Manager)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        $departments = User::select('department')->distinct()->whereNotNull('department')->pluck('department');

        return view('hr-admin.users.edit', [
            'user' => $user,
            'managers' => $managers,
            'departments' => $departments,
            'roles' => UserRole::toArray(),
        ]);
    }

    /**
     * Update a user.
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::enum(UserRole::class)],
            'department' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        DB::beginTransaction();

        try {
            // If role changed from non-employee to employee, initialize balances
            $roleChanged = $user->role->value !== $validated['role'];
            $becameEmployee = $roleChanged && $validated['role'] === UserRole::Employee->value;

            $user->update($updateData);

            if ($becameEmployee) {
                $this->leaveBalanceService->initializeBalances($user->id);
            }

            DB::commit();

            return redirect()
                ->route('hr-admin.users')
                ->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update user: '.$e->getMessage()]);
        }
    }

    /**
     * Display all leave balances with filtering.
     */
    public function balances(Request $request): View
    {

        $balances = LeaveBalance::with(['user'])
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('leave_type'), function ($query) use ($request) {
                $query->where('leave_type', $request->leave_type);
            })
            ->orderBy('user_id')
            ->orderBy('leave_type')
            ->paginate(20);

        $users = User::where('role', UserRole::Employee)->orderBy('name')->get();

        return view('hr-admin.balances.index', [
            'balances' => $balances,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for adjusting a balance.
     */
    public function editBalance(LeaveBalance $balance): View
    {
        return view('hr-admin.balances.edit', [
            'balance' => $balance->load('user'),
        ]);
    }

    /**
     * Update a leave balance.
     */
    public function updateBalance(Request $request, LeaveBalance $balance): RedirectResponse
    {

        $validated = $request->validate([
            'available_days' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $oldAvailable = $balance->available_days;
            $adjustment = $validated['available_days'] - $oldAvailable;

            $balance->update([
                'available_days' => $validated['available_days'],
            ]);

            // Record the adjustment in history
            $this->leaveBalanceService->recordBalanceHistory(
                $balance->user_id,
                $balance->leave_type,
                $adjustment,
                'manual_adjustment',
                null,
                'HR Admin adjustment: '.$validated['reason']
            );

            return redirect()
                ->route('hr-admin.balances')
                ->with('success', 'Balance updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update balance: '.$e->getMessage()]);
        }
    }

    /**
     * Display all company holidays.
     */
    public function holidays(Request $request): View
    {

        $holidays = CompanyHoliday::when($request->filled('year'), function ($query) use ($request) {
            $query->whereYear('date', $request->year);
        }, function ($query) {
            $query->whereYear('date', now()->year);
        })
            ->orderBy('date')
            ->get();

        return view('hr-admin.holidays.index', [
            'holidays' => $holidays,
            'selectedYear' => $request->filled('year') ? $request->year : now()->year,
        ]);
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function createHoliday(): View
    {
        return view('hr-admin.holidays.create');
    }

    /**
     * Store a newly created holiday.
     */
    public function storeHoliday(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:company_holidays,date',
            'is_recurring' => 'boolean',
        ]);

        CompanyHoliday::create($validated);

        return redirect()
            ->route('hr-admin.holidays')
            ->with('success', 'Holiday created successfully!');
    }

    /**
     * Show the form for editing a holiday.
     */
    public function editHoliday(CompanyHoliday $holiday): View
    {
        return view('hr-admin.holidays.edit', [
            'holiday' => $holiday,
        ]);
    }

    /**
     * Update a holiday.
     */
    public function updateHoliday(Request $request, CompanyHoliday $holiday): RedirectResponse
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => ['required', 'date', Rule::unique('company_holidays')->ignore($holiday->id)],
            'is_recurring' => 'boolean',
        ]);

        $holiday->update($validated);

        return redirect()
            ->route('hr-admin.holidays')
            ->with('success', 'Holiday updated successfully!');
    }

    /**
     * Delete a holiday.
     */
    public function destroyHoliday(CompanyHoliday $holiday): RedirectResponse
    {
        $holiday->delete();

        return redirect()
            ->route('hr-admin.holidays')
            ->with('success', 'Holiday deleted successfully!');
    }

    /**
     * Display all manager delegations system-wide.
     */
    public function delegations(Request $request): View
    {
        $delegations = ManagerDelegation::with(['manager', 'delegate'])
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->active()->where('end_date', '>=', now());
                } elseif ($request->status === 'inactive') {
                    $query->where(function ($q) {
                        $q->whereRaw('is_active = false')
                            ->orWhere('end_date', '<', now());
                    });
                }
            })
            ->when($request->filled('manager_id'), function ($query) use ($request) {
                $query->where('manager_id', $request->manager_id);
            })
            ->orderByDesc('start_date')
            ->paginate(20);

        $managers = User::where('role', UserRole::Manager)->orderBy('name')->get();

        return view('hr-admin.delegations', [
            'delegations' => $delegations,
            'managers' => $managers,
        ]);
    }

    /**
     * Toggle user active status (deactivate/activate).
     */
    public function toggleUserStatus(User $user): RedirectResponse
    {
        // Prevent HR admin from deactivating themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('hr-admin.users')
            ->with('success', "User account {$status} successfully!");
    }

    /**
     * Display company-wide reports.
     */
    public function reports(Request $request): View
    {

        $year = $request->filled('year') ? $request->year : now()->year;

        // Monthly leave trend
        $monthlyTrend = LeaveRequest::approved()
            ->whereYear('start_date', $year)
            ->select(DB::raw('EXTRACT(MONTH FROM start_date) as month'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_days) as total_days'))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM start_date)'))
            ->orderBy('month')
            ->get();

        // Leave type distribution
        $leaveTypeDistribution = LeaveRequest::approved()
            ->whereYear('start_date', $year)
            ->select('leave_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_days) as total_days'))
            ->groupBy('leave_type')
            ->get();

        // Department breakdown
        $departmentBreakdown = LeaveRequest::approved()
            ->join('users', 'leave_requests.user_id', '=', 'users.id')
            ->whereYear('leave_requests.start_date', $year)
            ->whereNotNull('users.department')
            ->select('users.department', DB::raw('COUNT(*) as count'), DB::raw('SUM(leave_requests.total_days) as total_days'))
            ->groupBy('users.department')
            ->get();

        // Approval rate
        $totalRequests = LeaveRequest::whereYear('submitted_at', $year)->count();
        $approvedRequests = LeaveRequest::approved()->whereYear('submitted_at', $year)->count();
        $approvalRate = $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 1) : 0;

        return view('hr-admin.reports', [
            'selectedYear' => $year,
            'monthlyTrend' => $monthlyTrend,
            'leaveTypeDistribution' => $leaveTypeDistribution,
            'departmentBreakdown' => $departmentBreakdown,
            'approvalRate' => $approvalRate,
            'totalRequests' => $totalRequests,
        ]);
    }
}
