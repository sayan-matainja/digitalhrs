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
        Schema::table('salary_revise_histories', function (Blueprint $table) {
            $table->double('hour_rate',10,2)->nullable();
            $table->double('weekly_hours',10,2)->nullable()->after('hour_rate');
            $table->double('monthly_hours',10,2)->nullable()->after('weekly_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_revise_histories', function (Blueprint $table) {
            $table->dropColumn('hour_rate');
            $table->dropColumn('weekly_hours');
            $table->dropColumn('monthly_hours');
        });
    }
};
