<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chief_name' => $this->chief_name,
            'officer_name' => $this->officer_name,
            'odts_code_old' => $this->odts_code_old,
            'odts_code' => $this->odts_code,
            'task_name' => $this->task_name,
            'activity' => $this->activity,
            'description' => $this->description,
            'assigned_at_old' => $this->assigned_at_old ? Carbon::parse($this->assigned_at_old)->format('m-d-Y') : null,
            'assigned_at' => $this->assigned_at ? Carbon::parse($this->assigned_at)->format('m-d-Y') : null,
            'is_done_old' => $this->is_done_old ? 'Done' : 'Not Done',
            'is_done' => $this->is_done ? 'Done' : 'Not Done',
            'created_at' => Carbon::parse($this->created_at)
                ->timezone('Asia/Manila')
                ->format('F j, Y h:i A'),
        ];

    }
}
