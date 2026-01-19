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
            $table->renameColumn('component_value_monthly','annual_component_value');
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
            $table->renameColumn('annual_component_value','component_value_monthly');
        });
    }
};
