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
        $task = AssignedTask::findOrFail($id);

        if ((int) $request->request_status === 1) {
            $task->update([
                'request_status' => 0,
                'is_done' => 1
            ]);

            return redirect()->back();
        }

        if ((int) $request->request_status === 2) {
            $task->update([
                'request_status' => 0,
                'is_done' => 0
            ]);

            return redirect()->back();
        }

        abort(404); 
    }
}
