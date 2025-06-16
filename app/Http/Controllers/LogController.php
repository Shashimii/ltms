<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use App\Http\Resources\ActivityLogResource;
use Inertia\Inertia;

class LogController extends Controller
{
    public function index(Request $request)
    {   
        $user = auth()->user();

        if ($user->role === User::ROLE_CHIEF) {
            $logsQuery = ActivityLog::search($request);
            $logs = ActivityLogResource::collection(
                $logsQuery->latest()->paginate(10)
            );
            
            return Inertia::render('Chief/Log', [
                'logs' => $logs,
                'search' => $request->search ?? '',
                'rangeFilter' => $request->rangeFilter ?? 'today',
                'status_filter' => $request->status_filter ?? '',
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {

            return Inertia::render('Officer/Log', [
            ]);
        }

        abort(403);
    }
}
