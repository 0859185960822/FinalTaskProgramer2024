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
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('project_name');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');

            $table->bigInteger('pm_id');
            $table->foreign('pm_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->integer('created_by')->nullable(false);
            $table->integer('updated_by')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
