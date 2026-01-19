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
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->after('is_paid');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('due_date');
            $table->date('repayment_from')->nullable()->after('issue_date');
            $table->double('repayment_amount','10,2')->nullable()->after('monthly_installment');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn('payment_date');

        });
        Schema::table('loans', function (Blueprint $table) {
            $table->date('due_date')->nullable();
            $table->dropColumn('repayment_from');
            $table->dropColumn('repayment_amount');

        });
    }
};
