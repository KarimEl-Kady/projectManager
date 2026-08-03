<?php

namespace App\Modules\Task\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'project_uuid' => $this->whenLoaded('project', fn () => $this->project->uuid),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->label(),
            'priority' => $this->priority->label(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'is_overdue' => $this->due_date?->isPast() && ! $this->due_date->isToday() && $this->status->label() !== 'done',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
