<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('path_photo', 255)->nullable();
            $table->enum('status', ['ENABLE', 'DISABLE'])->default('ENABLE');
            $table->integer('fail_login_count')->nullable()->default(0);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
        });
        // User::create([
        //     'name' => 'Admin Smartsoft 1',
        //     'username' => 'smartsoft',
        //     'password' => bcrypt("admin"),
        //     'avatar' => 'storage/user-profile/user_profile-20230206_10_17_38.jpg',
        //     'created_at' => now(),
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
