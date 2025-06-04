<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Relationships    
    public function assignedTask() {
        return $this->hasMany(AssignedTask::class, 'duty_id');
    }
}
