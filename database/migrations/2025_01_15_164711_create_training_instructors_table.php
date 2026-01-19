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
        Schema::create('training_instructors', function (Blueprint $table) {
            $table->id();
            $table->string('trainer_type');
            $table->unsignedBigInteger('training_id');
            $table->unsignedBigInteger('trainer_id');

            $table->foreign('training_id')->references('id')->on('trainings');
            $table->foreign('trainer_id')->references('id')->on('trainers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_instructors');
    }
};
