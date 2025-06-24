<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignedTaskRequest;
use App\Http\Requests\UpdateAssignedTaskRequest;
use App\Http\Resources\AssignedTaskResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\History;
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

        if ($auth->role === User::ROLE_CHIEF) {
            AssignedTask::create($request->validated());

            History::create([
                'task_id' => $request->task_id,
                'officer_id' => $request->officer_id,
                'odts_code' => $request->odts_code,
                'assigned_at' => $request->assigned_at,
                'activity' => 'Assigned',
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

        if ($auth->role === User::ROLE_CHIEF) { 

            History::create([
                'task_id' => $assignedTask->task_id,
                'officer_id' => $assignedTask->officer_id,
                'old_odts_code' => $assignedTask->odts_code,
                'old_assigned_at' => $assignedTask->assigned_at,
                'odts_code' => $request->odts_code,
                'assigned_at' => $request->assigned_at,
                'activity' => 'Edited'
            ]);

            $assignedTask->update($request->validated());

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

        if ($auth->role === User::ROLE_CHIEF) {
            $assignedTask->delete();

            History::create([
                'task_id' => $assignedTask->task_id,
                'officer_id' => $assignedTask->officer_id,
                'odts_code' => $assignedTask->odts_code,
                'assigned_at' => $assignedTask->assigned_at,
                'activity' => 'Deleted',
            ]);

            return redirect()->back()->with('toast', [
                'message' => 'Deleted Successfully.',
                'type' => 'warning'
            ]);
        }

        abort(403);        
    }

    public function done(Request $request)
    {
        $auth = auth()->user();
        $assignedTask = AssignedTask::findOrFail($request->id);
        
        if ($auth->role === User::ROLE_CHIEF) {
            $assignedTask->update([
                'is_done' => $request->is_done
            ]);

            History::create([
                'task_id' => $assignedTask->task_id,
                'officer_id' => $assignedTask->officer_id,
                'odts_code' => $assignedTask->odts_code,
                'assigned_at' => $assignedTask->assigned_at,
                'activity' => 'Done',
            ]);

            return redirect()->back()->with('toast', [
                'message' => 'Marked as Done.',
                'type' => 'success'
            ]);
        }

        abort(403);        
    }

        public function undone(Request $request)
    {
        $auth = auth()->user();
        $assignedTask = AssignedTask::findOrFail($request->id);
        
        if ($auth->role === User::ROLE_CHIEF) {
            $assignedTask->update([
                'is_done' => $request->is_done
            ]);

            History::create([
                'task_id' => $assignedTask->task_id,
                'officer_id' => $assignedTask->officer_id,
                'odts_code' => $assignedTask->odts_code,
                'assigned_at' => $assignedTask->assigned_at,
                'activity' => 'Undone',
            ]);

            return redirect()->back()->with('toast', [
                'message' => 'Marked as Undone.',
                'type' => 'success'
            ]);
        }

        abort(403);        
    }
}
