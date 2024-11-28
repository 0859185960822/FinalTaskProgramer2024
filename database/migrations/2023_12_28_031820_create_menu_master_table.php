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
        Schema::create('menu_masters', function (Blueprint $table) {
            $table->id('menu_master_id');
            $table->string('menu_master_name', 100)->nullable();
            $table->string('menu_master_type', 100)->nullable();
            $table->string('menu_master_icon', 100)->nullable();
            $table->string('menu_master_link', 100)->nullable();
            $table->integer('menu_master_urutan')->nullable();
            $table->bigInteger('menu_master_parent')->nullable();
            $table->string('menu_master_slug', 100);
            $table->enum('status', ['ENABLE', 'DISABLE'])->nullable()->default('ENABLE');
            $table->timestamps();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();

            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_masters');
    }
};
