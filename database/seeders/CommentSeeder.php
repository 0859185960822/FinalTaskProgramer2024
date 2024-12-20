<?php

namespace Database\Seeders;

// Import file models yang berelasi dengan comment
use App\Models\User;
use App\Models\Tasks;
//

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_id = User::first();
        $task_id = Tasks::first();
        DB::table('comments')->insert([
            'user_id' => $user_id->user_id,
            'task_id' => $task_id->task_id,
            'comment' => 'Ini adalah comment task',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
