<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class LogController extends Controller
{
    public function index(Request $request)
    {   
        $user = auth()->user();
        
        if ($user->role === User::ROLE_CHIEF) {
    
            return Inertia::render('Chief/Log', [
            ]);
        }

        if ($user->role === User::ROLE_OFFICER) {
            $logs = ActivityLog::where('officer_id', auth()->id())->get();
            return Inertia::render('Officer/Log', [
                'logs' => $logs
            ]);
        }

        abort(403);
    }
}
