<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['task_id', 'admin_id', 'chief_id', 'officer_id', 'task_name', 'admin_name', 'chief_name', 'officer_name', 'activity', 'description'];
}
