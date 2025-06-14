<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
            $query = ActivityLog::query();
            $now = Carbon::now('Asia/Manila'); // manila timezone
            $filter = $request->filter ?? 'today'; 
            
            if ($filter === 'today') {
                $query->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ?", [$now->toDateString()]);
            }

            if ($filter === 'week') {
                $startOfWeek = $now->copy()->startOfWeek()->toDateString();
                $endOfWeek = $now->copy()->endOfWeek()->toDateString();

                $query->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '+08:00')) BETWEEN ? AND ?", [$startOfWeek, $endOfWeek]);
            }

            if ($filter === 'month') {
                $query->whereRaw("MONTH(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ? AND YEAR(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ?", [$now->month, $now->year]);
            }

            return Inertia::render('Chief/Log', [
                'logs' => $query->get(),
                'selectedFilter' => $filter,
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
