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
            $table->foreignId('salary_group_id')->nullable()->constrained('salary_groups');
            $table->string('salary_cycle');
            $table->date('salary_from');
            $table->date('salary_to');
            $table->double('gross_salary',10,2);
            $table->double('tds',10,2);
            $table->double('advance_salary',10,2);
            $table->double('tada',10,2);
            $table->double('net_salary',10,2);
            $table->integer('total_days')->nullable();
            $table->integer('present_days')->nullable();
            $table->integer('absent_days')->nullable();
            $table->integer('leave_days')->nullable();

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
            $table->dropForeign(['salary_group_id']);
            $table->dropColumn('salary_group_id');
            $table->dropColumn('salary_cycle');
            $table->dropColumn('salary_from');
            $table->dropColumn('salary_to');
            $table->dropColumn('gross_salary');
            $table->dropColumn('tds');
            $table->dropColumn('advance_salary');
            $table->dropColumn('tada');
            $table->dropColumn('net_salary');
            $table->dropColumn('total_days');
            $table->dropColumn('present_days');
            $table->dropColumn('absent_days');
            $table->dropColumn('leave_days');
        });
    }
};
