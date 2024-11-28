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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id('role_permission_id');
            $table->bigInteger('role_id');
            $table->bigInteger('menu_master_id');
            $table->bigInteger('role_menu_id');
            $table->string('slug');
            $table->boolean('value')->nullable()->default('true');
            $table->timestamps();

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('cascade');
            $table->foreign('menu_master_id')->references('menu_master_id')->on('menu_masters')->onDelete('cascade');
            $table->foreign('role_menu_id')->references('role_menu_id')->on('role_menus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
