<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {   
        $user = auth()->user();
        
        if ($user->role === User::ROLE_CHIEF) {
            $logSearchQuery = ActivityLog::search($request);
            $logs = ActivityLogResource::collection($logSearchQuery->latest()->paginate(10));
            
            return Inertia::render('Chief/Log', [
                'logs' => $logs,
                'rangeFilter' => $request->filter ?? 'today',
                'activityFilter' => $request->activity_filter ?? '',
                'search' => $request->search ?? '',
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $logs = ActivityLog::where('officer_id', auth()->id())->get();
            return Inertia::render('Officer/Log', [
                'logs' => $logs,
            ]);
        }

        abort(403);
    }
}
