<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignedTaskResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index() {

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
            $assignedTasks = AssignedTaskResource::collection(AssignedTask::all()
            ->where('role', 0)
            ->where('officer_id', auth()->id()));

            return Inertia::render('Officer/Dashboard', [
                'assignedTasks' => $assignedTasks
            ]);
        }

        abort(403);
    }
}