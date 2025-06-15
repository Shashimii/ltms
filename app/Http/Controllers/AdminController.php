<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\ActivityLog;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function DashboardIndex(Request $request) {
        $admins = UserResource::collection(User::where('role', 2)->get());
        $chiefs = UserResource::collection(User::where('role', 1)->get());
        $officers = UserResource::collection(User::where('role', 0)->get());
        $users = UserResource::collection(User::all());

        $logSearchQuery = ActivityLog::search($request);
        $logs = ActivityLogResource::collection($logSearchQuery->latest()->paginate(10));

        return Inertia::render('Admin/Dashboard', [
            'admins' => $admins,
            'chiefs' => $chiefs,
            'officers' => $officers,
            'users' => $users,
            'logs' => $logs,
            'search' => $request->search ?? '',
        ]);
    }

    public function UserIndex(Request $request) {
        $usersQuery = User::search($request);
        $users = UserResource::collection($usersQuery->orderBy('created_at', 'desc')->paginate(10));
        return Inertia::render('Admin/User', [
            'search' => $request->search ?? '',
            'account_type' => $request->account_type ?? '',
            'users' => $users
        ]);
    }
}
