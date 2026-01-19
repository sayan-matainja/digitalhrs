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
        Schema::table('under_time_settings', function (Blueprint $table) {
            $table->string('ut_penalty_rate')->nullable()->change();
            $table->boolean('penalty_type')->default(1)->comment(" 0=percent, 1=amount");
            $table->double('penalty_percent',10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('under_time_settings', function (Blueprint $table) {
            $table->string('ut_penalty_rate')->nullable(false)->change();
            $table->dropColumn('penalty_type');
            $table->dropColumn('penalty_percent');
        });
    }
};
