<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssignedTaskResource;
use App\Models\AssignedTask;
use App\Models\User;
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
        $task = AssignedTask::findOrFail($id); // task

        if ($user->role === User::ROLE_CHIEF) {
            if ((int) $request->request_status === 1) {
                $task->update([
                    'request_status' => 0, // remove the request
                    'is_done' => 1 // set the task to done
                ]);

                return redirect()->back();
            }

            if ((int) $request->request_status === 2) {
                $task->update([
                    'request_status' => 0, // remove the request
                    'is_done' => 0 // set the task to not done
                ]);

                return redirect()->back();
            }
        }

        if ($user->role === User::ROLE_OFFICER) {
            if ($request->is_done) {
                $task->update([
                    'request_status' => 2 // request task is not done
                ]);

                return redirect()->back();
            } else {
                $task->update([
                    'request_status' => 1 // request task is done
                ]);

                return redirect()->back();
            }
        }

        abort(404); 
    }
}
