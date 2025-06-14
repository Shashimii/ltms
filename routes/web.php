<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssignedTaskController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestListController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Index
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->role === \App\Models\User::ROLE_ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === \App\Models\User::ROLE_CHIEF) {
            return redirect()->route('chief.dashboard');
        }

        if ($user->role === \App\Models\User::ROLE_OFFICER) {
            return redirect()->route('officer.dashboard');
        }
    }
    
    return Inertia::render('Auth/Login');
});

// Admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'DashboardIndex'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'UserIndex'])->name('admin.users');
    Route::get('/admin/log', [LogController::class, 'index'])->name('admin.log');

    // manage users
    Route::post('/admin/register', [RegisteredUserController::class, 'store'])->name('admin.register.store');
    Route::put('/admin/register/{id}', [RegisteredUserController::class, 'update'])->name('admin.register.update');
    Route::delete('/admin/register/{id}', [RegisteredUserController::class, 'destroy'])->name('admin.register.destroy');
});


// Legal Chief
Route::middleware(['auth', 'chief'])->group(function () {
    Route::get('/chief/dashboard', [DashboardController::class, 'index'])->name('chief.dashboard');
    Route::get('/chief/log', [LogController::class, 'index'])->name('chief.log');

    Route::resource('/chief/task', TaskListController::class)->names('chief.task');
    Route::resource('/chief/assigned-task', AssignedTaskController::class)->names('chief.assigned-task');
    Route::resource('/chief/notification', RequestListController::class)->names('chief.notification');
});


// Action Officer
Route::middleware(['auth', 'officer'])->group(function () {
    Route::get('/officer/dashboard', [DashboardController::class, 'index'])->name('officer.dashboard');
    Route::get('/officer/log', [LogController::class, 'index'])->name('officer.log');
    
    Route::resource('/officer/task', TaskListController::class)->names('officer.task');
    Route::resource('/officer/request', RequestListController::class)->names('officer.request');
    Route::resource('/officer/notification', RequestListController::class)->names('officer.notification');

    // Cancel Notify
    Route::put('/officer/notification/{id}/cancel', [RequestListController::class, 'cancelNotify'])->name('officer.notification.cancel');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
