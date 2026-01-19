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
        Schema::create('tax_report_tds_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_report_id');
            $table->foreign('tax_report_id')->references('id')->on('tax_reports')->onDelete('cascade');
            $table->integer('month');
            $table->double('amount',10,2)->default(0);
            $table->boolean('is_paid')->default(0);
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
        Schema::dropIfExists('tax_report_tds_details');
    }
};
