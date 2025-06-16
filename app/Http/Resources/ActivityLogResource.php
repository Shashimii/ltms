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
            'task_id' => $this->task_id,
            'admin_id' => $this->admin_id,
            'chief_id' => $this->chief_id,
            'officer_id' => $this->officer_id,
            'task_name' => $this->task_name,
            'odts_code' => $this->odts_code,
            'admin_name' => $this->admin_name,
            'chief_name' => $this->chief_name,
            'officer_name' => $this->officer_name,
            'activity' => $this->activity,
            'description' => $this->description,
            'created_at' => Carbon::parse($this->created_at)
                ->timezone('Asia/Manila')
                ->format('F j, Y g:i A'),
            'updated_at' => Carbon::parse($this->updated_at)
                ->timezone('Asia/Manila')
                ->format('F j, Y g:i A'),

        ];
    }
}
