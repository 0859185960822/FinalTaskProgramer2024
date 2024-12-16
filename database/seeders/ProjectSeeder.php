<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_id = \App\Models\User::first();
        DB::table('projects')->insert([
            'project_name'   => 'Membuat Web Task Management',
            'description'    => 'Membuat Web Untuk Memangement Sebuah Task Pribadi',
            'deadline'       => '2024-12-12',
            'pm_id'          => $user_id->user_id,
            'created_by'     => $user_id->user_id,
            'created_at'     => Carbon::now(),
        ]);
    }
}
