<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

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
        // dd($sisa_waktu);
        if($sisa_waktu === 1){
            $sisa_waktu = 'Hari Deadline';
        } elseif ($sisa_waktu <= 0) {
            $sisa_waktu = 'Deadline Terlewat';
        }
        
        return [
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'description' => $this->description,
            'deadline' => $this->deadline,
            'pm_id' => $this->whenLoaded('projectManager', function () {
                return [
                    'user_id' => $this->projectManager->user_id,
                    'name' => $this->projectManager->name,
                ];
            }),
            'collaborator' => $this->whenLoaded('teamMembers', function () {
                return $this->teamMembers->map(function ($member) {
                    return [
                        'user_id' => $member->user_id,
                        'name' => $member->name,
                        'username' => $member->username, // Contoh tambahan atribut
                    ];
                });
            }),
            'task' => $this->whenLoaded('task', function () {
                return $this->task->map(function ($task) { // Ubah sesuai format map()
                    return [
                        'task_id' => $task->task_id,
                        'project_id' => $task->project_id,
                        'collaborator_id' => $task->collaborator_id,
                        'task_name' => $task->task_name,
                        'status_task' => $task->status_task,
                        // 'created_at' =>
                    ];
                });
            }),
            'sisa_waktu' => $sisa_waktu,
        ];
    }
}
