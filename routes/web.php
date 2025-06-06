<?php

use App\Http\Controllers\AssignedTaskController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/admin/dashboard', function () { 
        return Inertia::render('Admin/Dashboard'); 
    })->name('admin.dashboard');
});


// Legal Chief
Route::middleware(['auth', 'chief'])->group(function () {
    Route::get('/chief/dashboard', [DashboardController::class, 'index'])->name('chief.dashboard');
    Route::resource('/chief/task', TaskListController::class)->names('chief.task');
    Route::resource('/chief/assigned-task', AssignedTaskController::class)->names('chief.assigned-task');
});


// Action Officer
Route::middleware(['auth', 'officer'])->group(function () {
    Route::get('/officer/dashboard', function () { 
        return Inertia::render('Officer/Dashboard'); 
    })->name('officer.dashboard');
    
    Route::resource('/officer/task', TaskListController::class)->names('officer.task');
    Route::resource('/officer/assigned-task', AssignedTaskController::class)->names('officer.assigned-task');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
