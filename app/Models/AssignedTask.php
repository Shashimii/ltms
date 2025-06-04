<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedTask extends Model
{
    use HasFactory;
    protected $fillable = ['officer_id', 'duty_id', 'odts_code', 'assigned_at', 'is_done'];
    protected $casts = [
        'assigned_at' => 'date:Y-m-d',  // Format the assigned_at column as a date
        'is_done' => 'boolean',         // Format is_done as a boolean
    ];

    // Relationships
    public function user()
    {
    return $this->belongsTo(User::class, 'officer_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
