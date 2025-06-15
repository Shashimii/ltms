<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLog extends Model
{
    protected $fillable = ['task_id', 'admin_id', 'chief_id', 'officer_id', 'odts_code', 'task_name', 'admin_name', 'chief_name', 'officer_name', 'activity', 'description'];


    public function scopeSearch(Builder $query, Request $request)
    {
        $now = Carbon::now('Asia/Manila');

        // Range Filter
        $filter = $request->range ?? 'today';

        if ($filter === 'today') {
            $query->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ?", [$now->toDateString()]);
        } elseif ($filter === 'week') {
            $startOfWeek = $now->copy()->startOfWeek()->toDateString();
            $endOfWeek = $now->copy()->endOfWeek()->toDateString();
            $query->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '+08:00')) BETWEEN ? AND ?", [$startOfWeek, $endOfWeek]);
        } elseif ($filter === 'month') {
            $query->whereRaw("MONTH(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ? AND YEAR(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ?", [$now->month, $now->year]);
        }

        // Activity Filter
        if ($request->filled('activity')) {
            $query->where('activity', $request->activity);
        }

        // Search Odts Code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('odts_code', 'like', "%{$search}%");
            });
        }

        return $query;
    }

}
