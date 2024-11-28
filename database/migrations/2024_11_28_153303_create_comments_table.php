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
        Schema::create('comments', function (Blueprint $table) {
            $table->id('comment_id');

            $table->bigInteger('task_id');
            $table->foreign('task_id')->references('task_id')->on('tasks')->onDelete('cascade');

            $table->bigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
