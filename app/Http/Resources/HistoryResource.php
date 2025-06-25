<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class HistoryResource extends JsonResource
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
            'task' => TaskResource::make($this->whenLoaded('task')),
            'officer' => UserResource::make($this->whenLoaded('officer')),
            'old_odts_code' => $this->old_odts_code,
            'odts_code' => $this->odts_code,
            'activity' => $this->activity,
            'old_assigned_at' => $this->old_assigned_at ? Carbon::parse($this->old_assigned_at)->format('m-d-Y') : null,
            'assigned_at' => $this->assigned_at ? Carbon::parse($this->assigned_at)->format('m-d-Y') : null,
        ];

    }
}
