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
        Schema::create('salary_group_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_group_id');
            $table->unsignedBigInteger('department_id');

            $table->foreign('salary_group_id')->references('id')->on('salary_groups');
            $table->foreign('department_id')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_group_departments');
    }
};
