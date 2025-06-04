<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssignedTaskResource;
use App\Http\Resources\UserResource;
use App\Models\AssignedTask;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignedTaskController extends Controller
{
    public function index()
    {
        $assignedDuties = AssignedTaskResource::collection(AssignedTask::paginate(10));
        $officers = UserResource::collection(User::where('role', 0)->get());

        return Inertia::render('Chief/AssignedTask/Index', [
            'search' => $request->search ?? '',
            'officer_id' => $request->officer_id ?? '',
            'status_filter' => $request->status ?? '',
            'assignedDuties' => $assignedDuties,
            'officers' => $officers
        ]);
    }
}
