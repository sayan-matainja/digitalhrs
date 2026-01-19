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
            $table->double('loan_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('loan_repayment_id')->nullable();
            $table->foreign('loan_repayment_id')->references('id')->on('loan_repayments')->onDelete('set null');
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
            $table->dropColumn('loan_amount');
            $table->dropForeign(['loan_repayment_id']);
            $table->dropColumn('loan_repayment_id');
        });
    }
};
