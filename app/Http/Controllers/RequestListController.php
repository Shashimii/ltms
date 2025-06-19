<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssignedTaskResource;
use App\Models\ActivityLog;
use App\Models\AssignedTask;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RequestListController extends Controller
{
    public function index(Request $request)
    {   
        $user = auth()->user();
        
        if ($user->role === User::ROLE_CHIEF) {
            $requestQuery = AssignedTask::search($request)->whereIn('request_status', [1, 2])->latest();
            $requests = AssignedTaskResource::collection($requestQuery->paginate(10));

            return Inertia::render('Chief/RequestList/Index', [
                'search' => $request->search ?? '',
                'requests' => $requests,
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $requestQuery = AssignedTask::search($request)->whereIn('request_status', [1, 2])->where('officer_id', auth()->id())->latest();
            $requests = AssignedTaskResource::collection($requestQuery->paginate(10));
            
            return Inertia::render('Officer/RequestList/Index', [
                'requests' => $requests,
            ]);
        }

        abort(403);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $task = AssignedTask::findOrFail($id); // task

        if ($user->role === User::ROLE_CHIEF) {
            if ($task->request_status != 0) {
                if ((int) $request->request_status === 1) {
                    $task->update([
                        'request_status' => 0, // remove the request
                        'is_done' => 1 // set the task to done
                    ]);

                    ActivityLog::create([
                        'task_id' => $request->task_id,
                        'chief_id' => auth()->id(),
                        'officer_id' => $request->officer_id,
                        'chief_name' => auth()->user()->name,
                        'officer_name' => $request->officer_name,
                        'odts_code' => $task->odts_code,
                        'task_name' => $request->task_name,
                        'activity' => 'Done_Confirmation',
                        'description' => auth()->user()->name . ' confirmed that "' . $request->officer_name . '" is "Done with the "' . $request->task_name .'"'
                    ]);

                    return redirect()->back()->with('toast', [
                        'message' => 'Confirmed successfully.',
                        'type' => 'success',
                    ]);
                }

                if ((int) $request->request_status === 2) {
                    $task->update([
                        'request_status' => 0, // remove the request
                        'is_done' => 0 // set the task to not done
                    ]);

                    ActivityLog::create([
                        'task_id' => $request->task_id,
                        'chief_id' => auth()->id(),
                        'officer_id' => $request->officer_id,
                        'chief_name' => auth()->user()->name,
                        'officer_name' => $request->officer_name,
                        'odts_code' => $task->odts_code,
                        'task_name' => $request->task_name,
                        'activity' => 'Not_Done_Confirmation',
                        'description' => auth()->user()->name . ' confirmed that "' . $request->officer_name . '" is "Not Done with the "' . $request->task_name .'"'

                    ]);

                    return redirect()->back()->with('toast', [
                        'message' => 'Confirmed successfully.',
                        'type' => 'success',
                    ]);
                }

                abort(404);
            }

            return redirect()->back()->with('toast', [
                'message' => 'This has already been canceled by the Officer nothing confirmed.',
                'type' => 'error'
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            if ($request->is_done && $task->is_done) {
                if ($task->request_status != 2) {
                    $task->update([
                        'request_status' => 2 // request task is not done
                    ]);

                    ActivityLog::create([
                        'task_id' => $request->task_id,
                        'officer_id' => auth()->id(),
                        'officer_name' => auth()->user()->name,
                        'odts_code' => $request->odts_code,
                        'task_name' => $request->task_name,
                        'activity' => 'Not_Done_Notify',
                        'description' => auth()->user()->name . ' notified the chief that the "' . $request->task_name . '" is "Not Done "'

                    ]);

                    return redirect()->back()->with('toast', [
                        'message' => 'The chief has been successfully notified!',
                        'type' => 'success'
                    ]);
                }

                return redirect()->back()->with('toast', [
                    'message' => 'Already notified the chief. wait for confirmation.',
                    'type' => 'error',
                ]);
            } 
            
            if (!$request->is_done && !$task->is_done) {
                if ($task->request_status !=1) {
                    $task->update([
                        'request_status' => 1 // request task is done
                    ]);

                    ActivityLog::create([
                        'task_id' => $request->task_id,
                        'officer_id' => auth()->id(),
                        'officer_name' => auth()->user()->name,
                        'odts_code' => $request->odts_code,
                        'task_name' => $request->task_name,
                        'activity' => 'Done_Notify',
                        'description' => auth()->user()->name . ' notified the chief that the "' . $request->task_name . '" is "Done "'

                    ]);

                    return redirect()->back()->with('toast', [
                        'message' => 'The chief has been successfully notified!',
                        'type' => 'success'
                    ]);                
                }

                return redirect()->back()->with('toast', [
                    'message' => 'Already notified the chief. wait for confirmation.',
                    'type' => 'error'
                ]);
            }

            return redirect()->back()->with('toast', [
                'message' => 'This has already been confirmed by the Chief. Please reload the page.',
                'type' => 'error'
            ]);
        }
        abort(403); 
    }

    public function cancelNotify(Request $request, $id)
    {
        $assigned_task = AssignedTask::findOrFail($id);
        $task = Task::findOrFail($assigned_task->task_id);
        
        if ($assigned_task->request_status != 0) {
            $assigned_task->update([
                'request_status' => 0
            ]);

            // done notify cancel
            if (!$request->is_done) {
                ActivityLog::create([
                    'task_id' => $task->id,
                    'officer_id' => auth()->id(),
                    'officer_name' => auth()->user()->name,
                    'odts_code' => $assigned_task->odts_code,
                    'task_name' => $task->name,
                    'activity' => 'Done_Notify_Cancel',
                    'description' => auth()->user()->name . ' canceled the done notify on "' . $task->name . '"'
                ]);
                
                return redirect()->back()->with('toast', [
                    'message' => 'Chief notify has been called off.',
                    'type' => 'error'
                ]);
            }

            // not done notify cancel
            if ($request->is_done) {
                ActivityLog::create([
                    'task_id' => $task->id,
                    'officer_id' => auth()->id(),
                    'officer_name' => auth()->user()->name,
                    'odts_code' => $assigned_task->odts_code,
                    'task_name' => $task->name,
                    'activity' => 'Not_Done_Notify_Cancel',
                    'description' => auth()->user()->name . ' canceled the not done notify on "' . $task->name . '"'
                ]); 
                
                return redirect()->back()->with('toast', [
                    'message' => 'Chief notify has been called off.',
                    'type' => 'error'
                ]);
            }
            
        } else {
            return redirect()->back()->with('toast', [
                'message' => 'Chief already confirmed this cannot be cancel.',
                'type' => 'error'
            ]);
        }

        abort(404); 
    }

    public function poll(Request $request)
    {
        $user = auth()->user();

        if ($user->role === User::ROLE_CHIEF) {
            $requestQuery = AssignedTask::search($request)->whereIn('request_status', [1, 2])->latest()->paginate(10);

            return AssignedTaskResource::collection($requestQuery);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $requestQuery = AssignedTask::search($request)->whereIn('request_status', [1, 2])->where('officer_id', auth()->id())->latest()->paginate(10);

            return AssignedTaskResource::collection($requestQuery);
        }

        abort(403);
    }
}
