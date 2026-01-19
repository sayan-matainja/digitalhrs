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
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->decimal('principal_amount',15,4)->change();
            $table->decimal('interest_amount',15,4)->change();
            $table->decimal('settlement_amount',15,4)->change();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('loan_amount',15,4)->change();
            $table->decimal('monthly_installment',15,4)->change()->nullable();
            $table->decimal('repayment_amount',15,4)->change()->nullable();
        });
        Schema::table('loan_settlement_requests', function (Blueprint $table) {
            $table->decimal('amount',15,4)->change()->nullable();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
