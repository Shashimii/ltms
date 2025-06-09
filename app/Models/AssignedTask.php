<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AssignedTask extends Model
{
    use HasFactory;
    protected $fillable = ['officer_id', 'task_id', 'odts_code', 'assigned_at', 'is_done'];
    protected $casts = [
        'assigned_at' => 'date:Y-m-d',  // Format the assigned_at column as a date
        'is_done' => 'boolean',         // Format is_done as a boolean
    ];

    // load the related officer and duty with assigned duty
    protected $with = ['officer', 'task'];

    // Relationships
    public function officer()
    {
    return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Search Query
    public function scopeSearch(Builder $query, Request $request)
    {
        // Officer Filter
        if ($request->filled('officer_id')) {
            $query->where('officer_id', $request->officer_id);
        };
        
        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_done', $request->status);
        }; 

        // Odts Code, Assigned at, Officer Name, Duty Name Searchbar
        return $query->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query
                    ->where('odts_code', 'like', '%' . $request->search . '%')
                    ->orWhere('assigned_at', 'like', '%' . $request->search . '%') 
                    
                    ->orWhereHas('officer', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%'); 
                    })
                    ->orWhereHas('task', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%'); 
                    });
            });
        });
    }
}
