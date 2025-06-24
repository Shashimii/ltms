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
            'odts_code' => $this->odts_code,
            'activity' => $this->activity,
            'created_at' => Carbon::parse($this->created_at)
                ->timezone('Asia/Manila')
                ->format('F j, Y h:i A'),
        ];

    }
}
