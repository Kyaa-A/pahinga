<?php

use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Leave Request routes
    Route::resource('leave-requests', LeaveRequestController::class)->except(['edit', 'update', 'destroy']);
    Route::post('leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');

    // Manager routes
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
        Route::get('/pending-requests', [ManagerController::class, 'pendingRequests'])->name('pending-requests');
        Route::get('/requests/{leave_request}', [ManagerController::class, 'showRequest'])->name('show-request');
        Route::post('/requests/{leave_request}/approve', [ManagerController::class, 'approve'])->name('approve');
        Route::post('/requests/{leave_request}/deny', [ManagerController::class, 'deny'])->name('deny');
        Route::get('/team-calendar', [ManagerController::class, 'teamCalendar'])->name('team-calendar');
    });
});

require __DIR__.'/auth.php';
