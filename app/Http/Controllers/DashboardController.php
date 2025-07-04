<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignedTaskResource;
use App\Http\Resources\HistoryResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\AssignedTask;
use App\Models\History;
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
            $requests = AssignedTaskResource::collection(AssignedTask::whereIn('request_status', [1, 2])->latest()->get());

            $query = History::search($request);
            $histories = HistoryResource::collection($query->with(['officer', 'task'])->latest()->paginate(3));

            return Inertia::render('Chief/Dashboard', [
                'officers' => $officers,
                'tasks' => $tasks,
                'assignedTasks' => $assignedTasks,
                'requests' => $requests,
                'histories' => $histories,
                'search' => $request->search ?? '',
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $assignedTasksQuery = AssignedTask::search($request)->with(['officer', 'task']);
            $assignedTasks = AssignedTaskResource::collection($assignedTasksQuery
            ->where('officer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10));

            $assignedTaskCount = AssignedTaskResource::collection(AssignedTask::where('officer_id', auth()->id())->get());
            $requests = AssignedTaskResource::collection(AssignedTask::whereIn('request_status', [1, 2])->where('officer_id', auth()->id())->latest()->get());

            return Inertia::render('Officer/Dashboard', [
                'search' => $request->search ?? '',
                'status_filter' => $request->status ?? '',
                'assignedTasks' => $assignedTasks,
                'assignedTaskCount' => $assignedTaskCount,
                'requests' => $requests,
            ]);
        }

        dd('Shibal');
    }
}