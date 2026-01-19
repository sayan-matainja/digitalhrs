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
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->jsonb('months')->nullable();
            $table->double('medical_claim',10,2)->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_reports', function (Blueprint $table) {
            $table->dropColumn('months');
            $table->dropColumn('medical_claim');
        });
    }
};
