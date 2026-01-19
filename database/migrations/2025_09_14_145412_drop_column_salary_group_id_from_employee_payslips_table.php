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
            $table->dropForeign(['salary_group_id']);
            $table->dropColumn('salary_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->foreignId('salary_group_id')->nullable()->constrained('salary_groups');
        });
    }
};
