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
        Schema::create('employee_payslip_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_payslip_id')->constrained('employee_payslips');
            $table->foreignId('salary_group_id')->nullable()->constrained('salary_groups');
            $table->string('salary_cycle');
            $table->string('payment_method');
            $table->string('payment_currency');
            $table->date('salary_from');
            $table->date('salary_to');
            $table->double('total_earning');
            $table->double('total_deduction');
            $table->double('net_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_payslip_details');
    }
};
