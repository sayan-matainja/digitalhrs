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
        Schema::create('tax_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('fiscal_year_id');
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->onDelete('cascade');
            $table->double('total_basic_salary',10,2)->default(0);
            $table->double('total_allowance',10,2)->default(0);
            $table->double('total_ssf_contribution',10,2)->default(0);
            $table->double('total_ssf_deduction',10,2)->default(0);
            $table->double('female_discount',10,2)->default(0);
            $table->double('other_discount',10,2)->default(0);
            $table->double('total_payable_tds',10,2)->default(0);
            $table->double('total_paid_tds',10,2)->default(0);
            $table->double('total_due_tds',10,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_reports');
    }
};
