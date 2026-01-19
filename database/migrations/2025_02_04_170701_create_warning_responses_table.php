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
        Schema::create('warning_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warning_id');
            $table->unsignedBigInteger('employee_id');
            $table->longText('message');

            $table->timestamps();
            $table->foreign('warning_id')->references('id')->on('warnings');
            $table->foreign('employee_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warning_responses');
    }
};
