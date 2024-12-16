<?php

namespace App\Http\Resources;

use App\Models\Projects;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin Projects
     */
class projectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sisa_waktu = Carbon::now()->diffInDays(Carbon::parse($this->deadline), false) +1;
        
        if($sisa_waktu === 1){
            $sisa_waktu = 'Hari Deadline';
        } elseif ($sisa_waktu <= 0) {
            $sisa_waktu = 'Deadline Terlewat';
        }

        // Format deadline dengan nama bulan
        $formattedDeadline = Carbon::parse($this->deadline)->translatedFormat('d F Y');

        // Hitung progress_project
        $totalTasks = $this->task ? $this->task->count() : 0;
        $doneTasks = $this->task ? $this->task->where('status_task', 'DONE')->count() : 0;
        $onGoingTasks = $this->task ? $this->task->where('status_task', 'ONGOING')->count() : 0;
        $pendingTasks = $this->task ? $this->task->where('status_task', 'PENDING')->count() : 0;

        $progress_project = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100, 2) : 0;
        
        return [
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'description' => $this->description,
            'deadline' => $formattedDeadline,
            'pm_id' => [
                    'user_id' => $this->projectManager->user_id,
                    'name' => $this->projectManager->name,
            ],
            'collaborator' => UserResource::collection($this->whenLoaded('teamMembers')),
            'task' => TaskResource::collection($this->whenLoaded('task')),
            'sisa_waktu' => $sisa_waktu,
            'total_task' => $totalTasks,
            'task_done' => $doneTasks,
            'task_pending' => $pendingTasks,
            'task_on_going' => $onGoingTasks,
            'progress_project' => $progress_project . '%',
        ];
    }
}
