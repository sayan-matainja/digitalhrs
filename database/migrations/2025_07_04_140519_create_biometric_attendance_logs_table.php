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
        Schema::create('biometric_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sn');
            $table->string('table');
            $table->string('stamp');
            $table->integer('employee_id');
            $table->dateTime('timestamp');
            $table->boolean('attendance_status')->nullable();
            $table->boolean('data_receive_status')->nullable();
            $table->boolean('workspace_id')->nullable();
            $table->boolean('status4')->nullable();
            $table->boolean('status5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_attendance_logs');
    }
};
