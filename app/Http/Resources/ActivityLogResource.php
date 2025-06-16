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
            'odts_code' => $this->odts_code,
            'task_name' => $this->task_name,
            'activity' => $this->activity,
            'description' => $this->description,
            'created_at' => Carbon::parse($this->created_at)
                ->timezone('Asia/Manila')
                ->format('F j, Y h:i A'),
        ];

    }
}
