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
        Schema::table('employee_payslip_details', function (Blueprint $table) {
            $table->dropForeign(['salary_group_id']);
            $table->dropColumn('salary_group_id');
            $table->dropColumn('salary_cycle');
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_currency');
            $table->dropColumn('salary_from');
            $table->dropColumn('salary_to');
            $table->dropColumn('total_earning');
            $table->dropColumn('total_deduction');
            $table->dropColumn('net_amount');

            $table->unsignedBigInteger('salary_component_id');
            $table->double('amount',10,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_payslip_details', function (Blueprint $table) {
            $table->foreignId('salary_group_id')->nullable()->constrained('salary_groups');
            $table->string('salary_cycle');
            $table->string('payment_method');
            $table->string('payment_currency');
            $table->date('salary_from');
            $table->date('salary_to');
            $table->double('total_earning');
            $table->double('total_deduction');
            $table->double('net_amount');

            $table->dropColumn('salary_component_id');
            $table->dropColumn('amount');
        });
    }
};
