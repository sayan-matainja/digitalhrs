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
        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->double('working_hours',10,2)->nullable();
            $table->double('worked_hours',10,2)->nullable();
            $table->double('overtime_hours',10,2)->nullable();
            $table->double('undertime_hours',10,2)->nullable();
            $table->double('hour_rate',10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->dropColumn('working_hours');
            $table->dropColumn('worked_hours');
            $table->dropColumn('overtime_hours');
            $table->dropColumn('undertime_hours');
            $table->dropColumn('hour_rate');
        });
    }
};
