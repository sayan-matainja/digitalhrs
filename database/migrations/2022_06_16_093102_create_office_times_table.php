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
        Schema::create('office_times', function (Blueprint $table) {
            $table->id();
            $table->time('opening_time');
            $table->time('closing_time');
            $table->string('shift');
            $table->string('category');
            $table->integer('holiday_count')->nullable();
            $table->longText('description')->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->boolean('is_active')->default('1');
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');

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
        Schema::dropIfExists('office_times');
    }
};
