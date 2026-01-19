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
        Schema::table('salary_revise_histories', function (Blueprint $table) {
            $table->double('base_monthly_salary')->nullable();
            $table->double('base_weekly_salary')->nullable();
            $table->double('base_monthly_allowance')->nullable();
            $table->double('base_weekly_allowance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_revise_histories', function (Blueprint $table) {
            $table->dropColumn('base_monthly_salary');
            $table->dropColumn('base_weekly_salary');
            $table->dropColumn('base_monthly_allowance');
            $table->dropColumn('base_weekly_allowance');
        });
    }
};
