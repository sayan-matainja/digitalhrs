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
            $table->double('absent_deduction')->default(0);
            $table->integer('holidays')->default(0);
            $table->integer('weekends')->default(0);
            $table->integer('paid_leave')->default(0);
            $table->integer('unpaid_leave')->default(0);
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
            $table->dropColumn('absent_deduction');
            $table->dropColumn('holidays');
            $table->dropColumn('weekends');
            $table->dropColumn('paid_leave');
            $table->dropColumn('unpaid_leave');
        });
    }
};
