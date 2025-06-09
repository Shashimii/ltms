<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskListController extends Controller
{
    public function index(Request $request)
    {   
        $user = auth()->user();
        
        if ($user->role === User::ROLE_CHIEF) {
            $tasksQuery = Task::search($request);
            $tasks = TaskResource::collection($tasksQuery->paginate(10));

            return Inertia::render('Chief/TaskList/Index', [
                'search' => $request->search ?? '',
                'tasks' => $tasks,
            ]);
        }

        abort(403);
    }

    public function store(StoreTaskRequest $request)
    {
        Task::create($request->validated());
        return redirect()->back();
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());
        return redirect()->back();
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->back();
    }
}
