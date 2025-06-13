<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignedTaskRequest;
use App\Http\Requests\UpdateAssignedTaskRequest;
use App\Http\Resources\AssignedTaskResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\AssignedTask;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignedTaskController extends Controller
{


    public function index(Request $request)
    {
        $auth = auth()->user();

        if ($auth->role === User::ROLE_CHIEF) {
            $assignedTasksQuery = AssignedTask::search($request)->with(['officer', 'task']);
            $assignedTasks = AssignedTaskResource::collection($assignedTasksQuery->orderBy('created_at', 'desc')->paginate(10));
            $officers = UserResource::collection(User::where('role', 0)->get());
            $tasks = TaskResource::collection(Task::all());

            return Inertia::render('Chief/AssignedTask/Index', [
                'search' => $request->search ?? '',
                'officer_id' => $request->officer_id ?? '',
                'status_filter' => $request->status ?? '',
                'assignedTasks' => $assignedTasks,
                'officers' => $officers,
                'tasks' => $tasks,
            ]);
        }

        abort(403);
    }

    
    public function store(StoreAssignedTaskRequest $request)
    {
        $auth = auth()->user();
        $officer_id = auth()->id();
        $officer = User::find($request->officer_id);
        $task = Task::find($request->task_id);

        if ($auth->role === User::ROLE_CHIEF) {
            AssignedTask::create($request->validated());
            ActivityLog::create([
                'task_id' => $request->task_id,
                'chief_id' => $officer_id,
                'officer_id' => $request->officer_id,
                'activity' => 'Assigned',
                'description' => $auth->name . ' assigned "' . $task->name . '" to "' . $officer->name .'"'

            ]);

            return redirect()->back();
        }

        // if ($auth->role === User::ROLE_OFFICER) {
        //     AssignedTask::create($request->validated());
        //     return redirect()->back();
        // }

        abort(403);        
    }

    public function update(UpdateAssignedTaskRequest $request, AssignedTask $assignedTask)
    {
        $auth = auth()->user();

        if ($auth->role === User::ROLE_CHIEF) {
            $assignedTask->update($request->validated());
            return redirect()->back();
        }

        // if ($auth->role === User::ROLE_OFFICER) {
        //     $assignedTask->update($request->validated());
        //     return redirect()->back();
        // }

        abort(403);        
    }

    public function destroy(AssignedTask $assignedTask)
    {
        $auth = auth()->user();

        if ($auth->role === User::ROLE_CHIEF) {
            $assignedTask->delete();
            return redirect()->back();   
        }

        // if ($auth->role === User::ROLE_OFFICER) {
        //     $assignedTask->delete();
        //     return redirect()->back();   
        // }

        abort(403);        
    }
}
