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

        $officers = UserResource::collection(User::where('role', 0)->get());
        $duties = TaskResource::collection( Task::all());
        $assignedTasks = AssignedTaskResource::collection(AssignedTask::all());

        return Inertia::render('Chief/Dashboard', [
            'officers' => $officers,
            'duties' => $duties,
            'assignedTasks' => $assignedTasks
        ]);
    }
}