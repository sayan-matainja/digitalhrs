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
        Schema::create('over_time_settings', function (Blueprint $table) {
            $table->id();
            $table->double('max_daily_ot_hours',5, 2);
            $table->double('max_weekly_ot_hours',5, 2);
            $table->double('max_monthly_ot_hours',5, 2);
            $table->double('valid_after_hour', 5, 2)->comment('after how many hours ot is valid');
            $table->double('overtime_pay_rate', 10, 2);
            $table->tinyInteger('is_active')->default(1)->comment('0= inactive, 1= active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('over_time_settings');
    }
};
