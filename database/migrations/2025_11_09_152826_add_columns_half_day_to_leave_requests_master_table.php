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
        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->string('leave_for')->default('full_day')->comment('in full_day, half_day');
            $table->string('leave_in')->nullable()->comment('in first_half, second_half');
            $table->dateTime('leave_to')->nullable()->change();
            $table->double('no_of_days',10,1)->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->dropColumn('leave_for');
            $table->dropColumn('leave_in');
        });
    }
};
