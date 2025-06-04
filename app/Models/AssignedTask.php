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
}
