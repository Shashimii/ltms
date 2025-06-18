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
        $officer_id = $request->officer_id;
        $officer = User::find($request->officer_id);
        $task = Task::find($request->task_id);

        if ($auth->role === User::ROLE_CHIEF) {
            AssignedTask::create($request->validated());

            ActivityLog::create([
                'task_id' => $request->task_id,
                'chief_id' => auth()->id(),
                'officer_id' => $officer_id,
                'chief_name' => $auth->name,
                'officer_name' => $officer->name,
                'odts_code' => $request->odts_code,
                'task_name' => $task->name,
                'activity' => 'Assigned',
                'description' => $auth->name . ' assigned "' . $task->name . '" to "' . $officer->name .'"'

            ]);

            return redirect()->back()->with('toast', [
                'message' => 'Assigned Successfully.',
                'type' => 'success'
            ]);
        }

        abort(403);        
    }

    public function update(UpdateAssignedTaskRequest $request, AssignedTask $assignedTask)
    {
        $auth = auth()->user();
        $officer = User::findOrFail($assignedTask->officer_id);
        $task = Task::findOrFail($assignedTask->task_id);

        if ($auth->role === User::ROLE_CHIEF) { 

            ActivityLog::create([
                'task_id' => $assignedTask->task_id,
                'chief_id' => auth()->id(),
                'officer_id' => $assignedTask->officer_id,
                'chief_name' => $auth->name,
                'officer_name' => $officer->name,
                'odts_code_old' => $assignedTask->odts_code,
                'odts_code' => $request->odts_code,
                'task_name' => $task->name,
                'assigned_at_old' => $assignedTask->assigned_at,
                'assigned_at' => $request->assigned_at,
                'is_done_old' => $assignedTask->is_done,
                'is_done' => $request->is_done,
                'activity' => 'Edited',
                'description' => $auth->name . ' make changes to "' . $task->name . '" assigned to "' . $officer->name .'" check new odts code "' . $request->odts_code .'"' 
            ]);

            $validated = $request->validated();
            $validated['request_status'] = 0;   // remove all the existing notify related to the task

            $assignedTask->update($validated);

            return redirect()->back()->with('toast', [
                'message' => 'Edited Successfully.',
                'type' => 'success'
            ]);
        }

        abort(403); 
    }

    public function destroy(AssignedTask $assignedTask)
    {
        $auth = auth()->user();
        $officer = User::findOrFail($assignedTask->officer_id);
        $task = Task::findOrFail($assignedTask->task_id);

        if ($auth->role === User::ROLE_CHIEF) {
            $assignedTask->delete();

            ActivityLog::create([
                'task_id' => $assignedTask->task_id,
                'chief_id' => auth()->id(),
                'officer_id' => $assignedTask->officer_id,
                'chief_name' => $auth->name,
                'officer_name' => $officer->name,
                'odts_code' => $assignedTask->odts_code,
                'task_name' => $task->name,
                'activity' => 'Deleted',
                'description' => $auth->name . ' deleted "' . $task->name . '" assigned to "' . $officer->name .'"'
            ]);

            return redirect()->back()->with('toast', [
                'message' => 'Deleted Successfully.',
                'type' => 'warning'
            ]); 
        }

        abort(403);        
    }
}
