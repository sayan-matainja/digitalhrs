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
        Schema::create('under_time_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('applied_after_minutes')->comment('after how many minutes ut is applicable');
            $table->double('ut_penalty_rate', 10, 2);
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
        Schema::dropIfExists('under_time_settings');
    }
};
