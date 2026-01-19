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
        Schema::table('tax_report_component_details', function (Blueprint $table) {
            $table->integer('month')->nullable()->after('tax_report_id');
        });
        Schema::table('tax_report_additional_details', function (Blueprint $table) {
            $table->integer('month')->nullable()->after('tax_report_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_report_component_details', function (Blueprint $table) {
            $table->dropColumn('month');
        });
        Schema::table('tax_report_additional_details', function (Blueprint $table) {
            $table->dropColumn('month');
        });

    }
};
