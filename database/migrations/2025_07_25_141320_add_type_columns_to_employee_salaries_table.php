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
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->string('payroll_type')->default('annual')->after('employee_id');
            $table->string('payment_type')->default('monthly')->after('payroll_type');
            $table->double('weekly_hours',10,2)->nullable()->after('hour_rate');
            $table->double('monthly_hours',10,2)->nullable()->after('basic_salary_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->dropColumn('payroll_type');
            $table->dropColumn('payment_type');
            $table->dropColumn('weekly_hours');
            $table->dropColumn('monthly_hours');
        });
    }
};
