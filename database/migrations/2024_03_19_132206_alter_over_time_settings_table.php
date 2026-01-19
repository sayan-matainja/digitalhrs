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
        Schema::table('over_time_settings', function (Blueprint $table) {
            $table->string('overtime_pay_rate')->nullable()->change();
            $table->boolean('pay_type')->default(1)->comment(" 0=percent, 1=amount");
            $table->double('pay_percent',10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('over_time_settings', function (Blueprint $table) {
            $table->string('overtime_pay_rate')->nullable(false)->change();
            $table->dropColumn('pay_type');
            $table->dropColumn('pay_percent');
        });
    }
};
