<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        // Redirect authenticated users based on role
        if (auth()->user()->isManager()) {
            return redirect()->route('manager.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    // Get all statistics in a single query using selectRaw
    $stats = $user->leaveRequests()
        ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as denied
        ', ['pending', 'approved', 'denied'])
        ->first();

    $totalRequests = $stats->total ?? 0;
    $pendingRequests = $stats->pending ?? 0;
    $approvedRequests = $stats->approved ?? 0;
    $deniedRequests = $stats->denied ?? 0;

    // Get upcoming approved leaves
    $upcomingLeaves = $user->leaveRequests()
        ->where('status', 'approved')
        ->where('start_date', '>=', now())
        ->orderBy('start_date')
        ->limit(5)
        ->get();

    // Get recent requests
    $recentRequests = $user->leaveRequests()
        ->with('manager')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    return view('dashboard', compact(
        'totalRequests',
        'pendingRequests',
        'approvedRequests',
        'deniedRequests',
        'upcomingLeaves',
        'recentRequests'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Leave Request routes
    Route::resource('leave-requests', LeaveRequestController::class)->except(['edit', 'update', 'destroy']);
    Route::post('leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Manager routes
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
        Route::get('/pending-requests', [ManagerController::class, 'pendingRequests'])->name('pending-requests');
        Route::get('/requests/{leave_request}', [ManagerController::class, 'showRequest'])->name('show-request');
        Route::post('/requests/{leave_request}/approve', [ManagerController::class, 'approve'])->name('approve');
        Route::post('/requests/{leave_request}/deny', [ManagerController::class, 'deny'])->name('deny');
        Route::get('/team-calendar', [ManagerController::class, 'teamCalendar'])->name('team-calendar');
        Route::get('/team-status', [ManagerController::class, 'teamStatus'])->name('team-status');
    });
});

require __DIR__.'/auth.php';
