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
            $requests = AssignedTaskResource::collection(AssignedTask::whereIn('request_status', [1, 2])->get());

            return Inertia::render('Chief/RequestList/Index', [
                'requests' => $requests,
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $requests = AssignedTaskResource::collection(AssignedTask::whereIn('request_status', [1, 2])
            ->where('officer_id', auth()->id())
            ->get());

            return Inertia::render('Officer/RequestList/Index', [
                'requests' => $requests,
            ]);
        }

        abort(403);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $officer = User::find($request->officer_id);
        $task = AssignedTask::findOrFail($id); // task 

        if ($user->role === User::ROLE_CHIEF) {
            if ((int) $request->request_status === 1) {
                $task->update([
                    'request_status' => 0, // remove the request
                    'is_done' => 1 // set the task to done
                ]);

                ActivityLog::create([
                    'task_id' => $request->task_id,
                    'chief_id' => auth()->id(),
                    'officer_id' => $request->officer_id,
                    'task_name' => $request->task_name,
                    'odts_code' => $request->odts_code,
                    'chief_name' => auth()->user()->name,
                    'officer_name' => $officer->name,
                    'activity' => 'Confirm_Done',
                    'description' => auth()->user()->name . ' confirmed that "' . $request->officer_name . '" is "Done with the "' . $request->task_name .'"'
                ]);

                return redirect()->back();
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
                    'task_name' => $request->task_name,
                    'odts_code' => $request->odts_code,
                    'chief_name' => auth()->user()->name,
                    'officer_name' => $officer->name,
                    'activity' => 'Confirm_Not_Done',
                    'description' => auth()->user()->name . ' confirmed that "' . $request->officer_name . '" is "Not Done with the "' . $request->task_name .'"'
                ]);

                return redirect()->back();
            }
        }

        if ($user->role === User::ROLE_OFFICER) {
            if ($request->is_done) {
                $task->update([
                    'request_status' => 2 // request task is not done
                ]);

                ActivityLog::create([
                    'task_id' => $request->task_id,
                    'officer_id' => auth()->id(),
                    'task_name' => $request->task_name,
                    'odts_code' => $request->odts_code,
                    'officer_name' => auth()->user()->name,
                    'activity' => 'Notify_Not_Done',
                    'description' => auth()->user()->name . ' notified the chief that the "' . $request->task_name . '" is "Not Done"'
                ]);

                return redirect()->back();
            }

            if (!$request->is_done) {
                $task->update([
                    'request_status' => 1 // request task is done
                ]);

                ActivityLog::create([
                    'task_id' => $request->task_id,
                    'officer_id' => auth()->id(),
                    'task_name' => $request->task_name,
                    'officer_name' => auth()->user()->name,
                    'odts_code' => $request->odts_code,
                    'activity' => 'Notify_Done',
                    'description' => auth()->user()->name . ' notified the chief that the "' . $request->task_name . '" is "Done"'
                ]);

                return redirect()->back();
            }
        }

        abort(404); 
    }

    public function cancel($id)
    {
        $request = AssignedTask::findOrFail($id);
        $task = Task::findOrFail($request->task_id);

        if ($request->request_status === 1) {
            $request->update([
                'request_status' => 0,
            ]); 

            ActivityLog::create([
                'task_id' => $request->task_id,
                'officer_id' => auth()->id(),
                'task_name' => $task->name,
                'officer_name' => auth()->user()->name,
                'odts_code' => $request->odts_code,
                'activity' => 'Cancel_Notify_Done',
                'description' => auth()->user()->name . ' canceled the done notify on "' . $task->name . '"'
            ]);

            return redirect()->back();
        }

        if ($request->request_status === 2) {
            $request->update([
                'request_status' => 0,
            ]); 

            ActivityLog::create([
                'task_id' => $request->task_id,
                'officer_id' => auth()->id(),
                'task_name' => $task->name,
                'officer_name' => auth()->user()->name,
                'odts_code' => $request->odts_code,
                'activity' => 'Cancel_Notify_Not_Done',
                'description' => auth()->user()->name . ' canceled the not done notify on "' . $task->name . '"'
            ]);

            return redirect()->back();
        }
    }

}
