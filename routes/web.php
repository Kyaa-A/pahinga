<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HRAdminController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Health Check Route (for container orchestration)
|--------------------------------------------------------------------------
*/

Route::get('/up', fn () => response('OK', 200));

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Employee Dashboard
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
    });

    // Leave Request routes (all authenticated users)
    Route::resource('leave-requests', LeaveRequestController::class)->except(['edit', 'update', 'destroy']);
    Route::post('leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
    Route::get('leave-requests/{leave_request}/attachment', [LeaveRequestController::class, 'downloadAttachment'])->name('leave-requests.download-attachment');
});

/*
|--------------------------------------------------------------------------
| Manager Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/pending-requests', [ManagerController::class, 'pendingRequests'])->name('pending-requests');
    Route::get('/requests/{leave_request}', [ManagerController::class, 'showRequest'])->name('show-request');
    Route::post('/requests/{leave_request}/approve', [ManagerController::class, 'approve'])->name('approve');
    Route::post('/requests/{leave_request}/deny', [ManagerController::class, 'deny'])->name('deny');
    Route::get('/team-calendar', [ManagerController::class, 'teamCalendar'])->name('team-calendar');
    Route::get('/team-status', [ManagerController::class, 'teamStatus'])->name('team-status');

    // Delegation routes
    Route::get('/delegations', [ManagerController::class, 'delegations'])->name('delegations');
    Route::post('/delegations', [ManagerController::class, 'storeDelegation'])->name('delegations.store');
    Route::post('/delegations/{delegation}/deactivate', [ManagerController::class, 'deactivateDelegation'])->name('delegations.deactivate');
    Route::delete('/delegations/{delegation}', [ManagerController::class, 'destroyDelegation'])->name('delegations.destroy');
});

/*
|--------------------------------------------------------------------------
| HR Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'hr_admin'])->prefix('hr-admin')->name('hr-admin.')->group(function () {
    Route::get('/dashboard', [HRAdminController::class, 'dashboard'])->name('dashboard');

    // User management
    Route::get('/users', [HRAdminController::class, 'users'])->name('users');
    Route::get('/users/create', [HRAdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [HRAdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [HRAdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [HRAdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/toggle-status', [HRAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');

    // Delegation tracking
    Route::get('/delegations', [HRAdminController::class, 'delegations'])->name('delegations');

    // Balance management
    Route::get('/balances', [HRAdminController::class, 'balances'])->name('balances');
    Route::get('/balances/{balance}/edit', [HRAdminController::class, 'editBalance'])->name('balances.edit');
    Route::put('/balances/{balance}', [HRAdminController::class, 'updateBalance'])->name('balances.update');

    // Holiday management
    Route::get('/holidays', [HRAdminController::class, 'holidays'])->name('holidays');
    Route::get('/holidays/create', [HRAdminController::class, 'createHoliday'])->name('holidays.create');
    Route::post('/holidays', [HRAdminController::class, 'storeHoliday'])->name('holidays.store');
    Route::get('/holidays/{holiday}/edit', [HRAdminController::class, 'editHoliday'])->name('holidays.edit');
    Route::put('/holidays/{holiday}', [HRAdminController::class, 'updateHoliday'])->name('holidays.update');
    Route::delete('/holidays/{holiday}', [HRAdminController::class, 'destroyHoliday'])->name('holidays.destroy');

    // Reports
    Route::get('/reports', [HRAdminController::class, 'reports'])->name('reports');
});

require __DIR__.'/auth.php';
