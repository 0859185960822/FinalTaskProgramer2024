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
        Schema::create('role_menus', function (Blueprint $table) {
            $table->id('role_menu_id');
            $table->bigInteger('role_id')->unsigned()->nullable();
            $table->bigInteger('menu_master_id')->unsigned()->nullable();
            // $table->integer('urutan_menu_master')->nullable();
            $table->timestamps();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();

            $table->foreign('menu_master_id')->references('menu_master_id')->on('menu_masters')->onDelete('cascade');
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menus');
    }
};
