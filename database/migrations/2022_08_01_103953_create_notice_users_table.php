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
        Schema::create('notice_receivers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('notice_id')->unsigned();
            $table->bigInteger('notice_receiver_id')->unsigned();

            $table->foreign('notice_id')->references('id')->on('notices');
            $table->foreign('notice_receiver_id')->references('id')->on('users');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_users');
    }
};
