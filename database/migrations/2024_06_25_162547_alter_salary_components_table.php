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
        Schema::table('salary_components', function (Blueprint $table) {
            $table->double('annual_component_value')->nullable()->change();
            $table->boolean('apply_for_all')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->double('annual_component_value')->nullable(false)->change();
            $table->dropColumn('apply_for_all');
        });
    }
};
