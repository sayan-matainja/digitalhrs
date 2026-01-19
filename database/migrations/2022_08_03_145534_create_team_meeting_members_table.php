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
        Schema::create('team_meeting_members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('team_meeting_id')->unsigned();
            $table->bigInteger('meeting_participator_id')->unsigned();

            $table->foreign('team_meeting_id')->references('id')->on('team_meetings');
            $table->foreign('meeting_participator_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_meeting_members');
    }
};
