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
        Schema::create('tada_attachments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tada_id')->unsigned();
            $table->string('attachment');

            $table->foreign('tada_id')->references('id')->on('tadas')->cascadeOnDelete();
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
        Schema::dropIfExists('tada_attachments');
    }
};
