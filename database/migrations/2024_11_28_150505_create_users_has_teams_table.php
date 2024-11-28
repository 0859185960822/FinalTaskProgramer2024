<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_has_teams', function (Blueprint $table) {
            $table->id('user_id');
            $table->bigInteger('project_id');

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_has_teams');
    }
};
