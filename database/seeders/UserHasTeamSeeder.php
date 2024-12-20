<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;

class UserHasTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project_id = \App\Models\Projects::first();
        $user_id = \App\Models\User::first();
        DB::table('users_has_teams')->insert([
            'users_id'       => $user_id->user_id,
            'project_id'    => $project_id->project_id,
            'created_at'     => Carbon::now(),
        ]);
    }
}
