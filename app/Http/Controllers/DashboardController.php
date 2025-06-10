<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignedTaskResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request) {

        $user = auth()->user();

        if ($user->role === User::ROLE_CHIEF) {
            $officers = UserResource::collection(User::where('role', 0)->get());
            $tasks = TaskResource::collection( Task::all());
            $assignedTasks = AssignedTaskResource::collection(AssignedTask::all());

            return Inertia::render('Chief/Dashboard', [
                'officers' => $officers,
                'tasks' => $tasks,
                'assignedTasks' => $assignedTasks
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $assignedTasksQuery = AssignedTask::search($request)->with(['officer', 'task']);
            $assignedTasks = AssignedTaskResource::collection($assignedTasksQuery
            ->where('officer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10));

            $assignedTaskCount = AssignedTaskResource::collection(AssignedTask::where('officer_id', auth()->id())->get());

            return Inertia::render('Officer/Dashboard', [
                'search' => $request->search ?? '',
                'status_filter' => $request->status ?? '',
                'assignedTasks' => $assignedTasks,
                'assignedTaskCount' => $assignedTaskCount,
            ]);
        }

        abort(403);
    }
}