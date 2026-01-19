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
        Schema::create('over_time_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('over_time_setting_id');
            $table->unsignedBigInteger('department_id');

            $table->foreign('over_time_setting_id')->references('id')->on('over_time_settings');
            $table->foreign('department_id')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('over_time_departments');
    }
};
