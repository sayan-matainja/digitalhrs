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
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->double('annual_salary',10,2);
            $table->string('basic_salary_type')->default('percent')->comment("in:'percent','fixed'");
            $table->double('basic_salary_value',10,2)->nullable();
            $table->double('monthly_basic_salary',10,2);
            $table->double('annual_basic_salary',10,2);
            $table->double('monthly_fixed_allowance',10,2);
            $table->double('annual_fixed_allowance',10,2);
            $table->unsignedBigInteger('salary_group_id')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('users');
            $table->foreign('salary_group_id')->references('id')->on('salary_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_salaries');
    }
};
