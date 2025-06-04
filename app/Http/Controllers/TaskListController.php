<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskListController extends Controller
{
    public function index()
    {   
        $user = auth()->user();

        if ($user->role === User::ROLE_CHIEF) {
            $tasks = TaskResource::collection(Task::paginate(10));

            return Inertia::render('Chief/TaskList/Index', [
                'search' => $request->search ?? '',
                'tasks' => $tasks,
            ]);
        }

        abort(403);
    }
}
