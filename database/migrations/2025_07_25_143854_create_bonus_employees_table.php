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
        Schema::create('bonus_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bonus_id');
            $table->unsignedBigInteger('employee_id');

            $table->foreign('bonus_id')->references('id')->on('bonuses');
            $table->foreign('employee_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_employees');
    }
};
