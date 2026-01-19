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
            $table->jsonb('tada_ids')->nullable();
            $table->jsonb('advance_salary_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->dropColumn('tada_ids');
            $table->dropColumn('advance_salary_ids');
        });
    }
};
