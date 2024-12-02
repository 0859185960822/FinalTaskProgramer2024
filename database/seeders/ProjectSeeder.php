<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Projects::create([
            'project_name'   => 'Membuat Web Task Management',
            'description'    => 'Membuat Web Untuk Memangement Sebuah Task Pribadi',
            'deadline'       => '2024-12-12',
            'pm_id'          => 1,
            'created_by'     => 1,
            'created_at'     => Carbon::now(),
        ]);
    }
}
