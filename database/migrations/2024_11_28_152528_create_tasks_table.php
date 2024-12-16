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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id('task_id');

            $table->bigInteger('project_id');
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');

            $table->bigInteger('collaborator_id');
            $table->foreign('collaborator_id')->references('user_id')->on('users')->onDelete('cascade');

            $table->string('task_name');
            $table->enum('priority_task',[1,2,3])->default(1);
            $table->enum('type_task',['MAJOR','MINOR']);
            $table->enum('status_task', ['PENDING', 'ON GOING', 'DONE'])->default('PENDING');
            $table->timestamps();
            $table->integer('created_by')->nullable(true);
            $table->integer('updated_by')->nullable(true);
            $table->softDeletes();
            $table->date('deadline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
