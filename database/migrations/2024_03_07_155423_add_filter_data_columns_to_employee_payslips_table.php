<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->boolean('include_tada')->default(1);
            $table->boolean('include_advance_salary')->default(1);
            $table->boolean('attendance')->default(1);
            $table->boolean('absent_paid')->default(0);
            $table->boolean('approved_paid_leaves')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->dropColumn('include_tada');
            $table->dropColumn('include_advance_salary');
            $table->dropColumn('attendance');
            $table->dropColumn('absent_paid');
            $table->dropColumn('approved_paid_leaves');
        });
    }
};
