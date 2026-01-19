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
        Schema::table('ssf', function (Blueprint $table) {
            $table->boolean('enable_tax_exemption')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ssf', function (Blueprint $table) {
            $table->dropColumn('enable_tax_exemption');
        });
    }
};
