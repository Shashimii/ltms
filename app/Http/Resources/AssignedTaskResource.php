<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignedTaskResource extends JsonResource
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
            'officer' => UserResource::make($this->whenLoaded('user')),
            'task' => TaskResource::make($this->whenLoaded('task')),
            'odts_code' => $this->odts_code,
            'is_done' => $this->is_done,
            'assigned_at' => $this->assigned_at->format('m-d-Y')
        ];
    }
}
