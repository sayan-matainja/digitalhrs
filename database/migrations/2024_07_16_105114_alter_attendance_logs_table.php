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
        Schema::table('attendance_logs', function (Blueprint $table) {

            $table->dropForeign(['attendance_id']);
            $table->dropColumn('attendance_id');
            $table->dropColumn('type');
            $table->dropColumn('time');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');

            // new columns
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('attendance_type')->nullable();
            $table->string('identifier')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_id');
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->string('type');
            $table->time('time');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();

            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
            $table->dropColumn('attendance_type');
            $table->dropColumn('identifier');
        });
    }
};
