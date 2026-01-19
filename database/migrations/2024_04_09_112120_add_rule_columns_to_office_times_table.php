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
        Schema::table('office_times', function (Blueprint $table) {
            $table->boolean('is_early_check_in')->default(0);
            $table->integer('checkin_before')->nullable()->comment('in minutes');
            $table->boolean('is_early_check_out')->default(0);
            $table->integer('checkout_before')->nullable()->comment('in minutes');
            $table->boolean('is_late_check_in')->default(0);
            $table->integer('checkin_after')->nullable()->comment('in minutes');
            $table->boolean('is_late_check_out')->default(0);
            $table->integer('checkout_after')->nullable()->comment('in minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('office_times', function (Blueprint $table) {
            $table->dropColumn('is_early_check_in');
            $table->dropColumn('checkin_before');
            $table->dropColumn('is_early_check_out');
            $table->dropColumn('checkout_before');
            $table->dropColumn('is_late_check_in');
            $table->dropColumn('checkin_after');
            $table->dropColumn('is_late_check_out');
            $table->dropColumn('checkout_after');
        });
    }
};
