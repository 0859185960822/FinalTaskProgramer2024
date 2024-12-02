<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project_id = \App\Models\Projects::first();
        $user_id = \App\Models\User::first();
        \App\Models\Tasks::create([
            'project_id'     => $project_id->project_id,
            'collaborator_id'=> $user_id->user_id,
            'task_name'      => 'Testing Api',
            'type_task'      => 'MAJOR',
            'priority_task'  => 1,
            'status_task'    => 'PENDING',
            'created_by'     => $user_id->user_id,
        ]);
    }
}
