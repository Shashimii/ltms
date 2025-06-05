<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssignedTaskResource;
use App\Http\Resources\UserResource;
use App\Models\AssignedTask;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignedTaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === User::ROLE_CHIEF) {
            $assignedTasksQuery = AssignedTask::search($request)->with(['officer', 'task']);
            $assignedTasks = AssignedTaskResource::collection($assignedTasksQuery->paginate(10));
            $officers = UserResource::collection(User::where('role', 0)->get());

            return Inertia::render('Chief/AssignedTask/Index', [
                'search' => $request->search ?? '',
                'officer_id' => $request->officer_id ?? '',
                'status_filter' => $request->status ?? '',
                'assignedTasks' => $assignedTasks,
                'officers' => $officers
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $assignedTasks = AssignedTaskResource::collection(AssignedTask::paginate(10));

            return Inertia::render('Officer/AssignedTask/Index', [
                'search' => $request->search ?? '',
                'officer_id' => $request->officer_id ?? '',
                'status_filter' => $request->status ?? '',
                'assignedTasks' => $assignedTasks,
            ]);
        }

        abort(403);
    }
}
