<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\CustomersSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /**
         * FIle ini untuk mendaftarkan masing-masing file seeder di DatabaseSeeder.php
         * Dengan menggunakan: $this-call(Nama file seeder::class);
        */

        // \App\Models\User::factory(10)->create();
        $this->call(ConfigSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(MenuMasterSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(RoleMenuSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(TaskSeeder::class);
        $this->call(CommentSeeder::class);
        $this->call(UserHasTeamSeeder::class);
    }
}
