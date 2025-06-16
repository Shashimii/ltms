<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ActivityLog extends Model
{
    protected $fillable = [
        'task_id', 
        'admin_id', 
        'chief_id', 
        'officer_id',
        'odts_code',
        'task_name',
        'admin_name',
        'chief_name',
        'officer_name', 
        'activity', 
        'description'
    ];

    
public function scopeSearch(Builder $query, Request $request)
{
    if ($request->filled('activity')) {
        $query->where('activity', $request->activity);
    }

    // Default to 'today' if range not provided
    $range = $request->range ?? 'today';
    $now = Carbon::now('Asia/Manila');

    if ($range === 'today') {
        $query->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ?", [$now->toDateString()]);
    } elseif ($range === 'week') {
            $startOfWeek = $now->copy()->startOfWeek()->startOfDay()->toDateTimeString();
            $endOfWeek = $now->copy()->endOfWeek()->endOfDay()->toDateTimeString();

            $query->whereRaw(
                "CONVERT_TZ(created_at, '+00:00', '+08:00') BETWEEN ? AND ?",
                [$startOfWeek, $endOfWeek]
            );
        } elseif ($range === 'month') {
        $query->whereRaw(
            "MONTH(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ? AND YEAR(CONVERT_TZ(created_at, '+00:00', '+08:00')) = ?",
            [$now->month, $now->year]
        );
    }

    // ODTS Code / Search Filter
    return $query->when($request->search, function ($query) use ($request) {
        $query->where(function ($query) use ($request) {
            $query->where('odts_code', 'like', '%' . $request->search . '%');
        });
    });
}

}
