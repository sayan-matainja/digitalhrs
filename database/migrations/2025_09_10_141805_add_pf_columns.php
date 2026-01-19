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
            $table->double('pf_deduction',10,2)->default(0);
            $table->double('pf_contribution',10,2)->default(0);
        });
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->double('total_pf_contribution',10,2)->default(0);
            $table->double('total_pf_deduction',10,2)->default(0);
        });
        Schema::table('tax_report_details', function (Blueprint $table) {
            $table->double('pf_deduction',10,2)->default(0);
            $table->double('pf_contribution',10,2)->default(0);
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
            $table->dropColumn('pf_deduction');
            $table->dropColumn('pf_contribution');
        });
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropColumn('total_pf_contribution');
            $table->dropColumn('total_pf_deduction');
        });
        Schema::table('tax_report_details', function (Blueprint $table) {
            $table->dropColumn('pf_deduction');
            $table->dropColumn('pf_contribution');
        });
    }
};
