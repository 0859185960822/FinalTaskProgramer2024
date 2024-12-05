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
            // 'collaborator' => $this->whenLoaded('teamMembers', function () {
            //     return $this->teamMembers->map(function ($member) {
            //         return [
            //             'user_id' => $member->user_id,
            //             'name' => $member->name,
            //             'username' => $member->username, // Contoh tambahan atribut
            //         ];
            //     });
            // }),
            'collaborator' => UserResource::collection($this->whenLoaded('teamMembers')),
            // 'task' => $this->whenLoaded('task', function () {
            //     return $this->task->map(function ($task) { // Ubah sesuai format map()
            //         return [
            //             'task_id' => $task->task_id,
            //             'project_id' => $task->project_id,
            //             'collaborator_id' => $task->collaborator_id,
            //             'task_name' => $task->task_name,
            //             'status_task' => $task->status_task,
            //         ];
            //     });
            // }),
            'task' => TaskResource::collection($this->whenLoaded('task')),
            'sisa_waktu' => $sisa_waktu,
            'progress_project' => $progress_project . '%',
        ];
    }
}
