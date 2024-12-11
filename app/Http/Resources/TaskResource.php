<?php

namespace App\Http\Resources;

use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin Tasks
     */
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'task_id' => $this->task_id,
            'project_id' => $this->project,
            'collaborator_id' => [
                'user_id' => $this->collaborator->user_id,
                'name' => $this->collaborator->name,
            ],
            'task_name' => $this->task_name,
            'priority_task' => $this->priority_task,
            'type_task' => $this->type_task,
            'status_task' => $this->status_task,
        ];
    }
}
