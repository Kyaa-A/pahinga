<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequestFormRequest;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        // Authorization will be handled in routes or individual methods
    }

    /**
     * Display a listing of the user's leave requests.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = LeaveRequest::with(['manager'])
            ->where('user_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->orderByDesc('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('leave-requests.index', [
            'leaveRequests' => $leaveRequests,
        ]);
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create(): View
    {
        $leaveTypes = [
            'paid_time_off' => 'Paid Time Off',
            'unpaid_leave' => 'Unpaid Leave',
            'sick_leave' => 'Sick Leave',
            'vacation' => 'Vacation',
        ];

        return view('leave-requests.create', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(LeaveRequestFormRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user has a manager assigned
        if (! $user->hasManager()) {
            return back()
                ->withErrors(['manager' => 'You do not have a manager assigned. Please contact HR.'])
                ->withInput();
        }

        // Create the leave request
        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'manager_id' => $user->manager_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'pending',
            'employee_notes' => $request->employee_notes,
            'submitted_at' => now(),
        ]);

        // Record history
        $leaveRequest->recordHistory('submitted', $user->id, 'Leave request submitted');

        // Notify manager
        $user->manager->notify(new \App\Notifications\LeaveRequestSubmittedNotification($leaveRequest));

        return redirect()
            ->route('leave-requests.show', $leaveRequest)
            ->with('success', 'Your leave request has been submitted successfully!');
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['user', 'manager', 'history.performedBy']);

        return view('leave-requests.show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    /**
     * Cancel the specified leave request.
     */
    public function cancel(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        // Check if user owns this request
        if ($leaveRequest->user_id !== $request->user()->id) {
            abort(403, 'You can only cancel your own requests.');
        }

        // Check if request can be cancelled
        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending requests can be cancelled.']);
        }

        $leaveRequest->update([
            'status' => 'cancelled',
        ]);

        $leaveRequest->recordHistory(
            'cancelled',
            $request->user()->id,
            'Cancelled by employee'
        );

        // Notify manager
        $leaveRequest->manager->notify(new \App\Notifications\LeaveRequestCancelledNotification($leaveRequest));

        return redirect()
            ->route('leave-requests.index')
            ->with('success', 'Your leave request has been cancelled.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not implemented for MVP
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Not implemented for MVP
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Not implemented - use cancel instead
        abort(404);
    }
}
