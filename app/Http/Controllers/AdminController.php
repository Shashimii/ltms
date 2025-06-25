<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\HistoryResource;
use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function DashboardIndex(Request $request) {
        $admins = UserResource::collection(User::where('role', 2)->get());
        $chiefs = UserResource::collection(User::where('role', 1)->get());
        $officers = UserResource::collection(User::where('role', 0)->get());
        $users = UserResource::collection(User::all());

        $query = History::search($request);
        $histories = HistoryResource::collection($query->with(['officer', 'task'])->latest()->paginate(10));

        return Inertia::render('Admin/Dashboard', [
            'admins' => $admins,
            'chiefs' => $chiefs,
            'officers' => $officers,
            'users' => $users,
            'histories' => $histories,
            'rangeFilter' => $request->filter ?? 'Today',
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
