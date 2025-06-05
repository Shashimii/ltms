<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Task extends Model
{
    use HasFactory;
    
    // Relationships    
    public function assignedTask() {
        return $this->hasMany(AssignedTask::class, 'task_id');
    }

    // Search Query
    public function scopeSearch(Builder $query, Request $request)
    {
        return $query->when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        });
    }
}
