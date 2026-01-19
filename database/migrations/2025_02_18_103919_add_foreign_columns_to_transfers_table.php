<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfers', function (Blueprint $table) {

            $table->unsignedBigInteger('old_post_id')->nullable();
            $table->foreign('old_post_id')->references('id')->on('posts');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->foreign('post_id')->references('id')->on('posts');
            $table->unsignedBigInteger('old_office_time_id')->nullable();
            $table->foreign('old_office_time_id')->references('id')->on('office_times');
            $table->unsignedBigInteger('office_time_id')->nullable();
            $table->foreign('office_time_id')->references('id')->on('office_times');
            $table->unsignedBigInteger('old_supervisor_id')->nullable();
            $table->foreign('old_supervisor_id')->references('id')->on('users');
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->foreign('supervisor_id')->references('id')->on('users');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropForeign(['old_post_id']);
            $table->dropColumn('old_post_id');
            $table->dropForeign(['post_id']);
            $table->dropColumn('post_id');
            $table->dropForeign(['old_office_time_id']);
            $table->dropColumn('old_office_time_id');
            $table->dropForeign(['office_time_id']);
            $table->dropColumn('office_time_id');
            $table->dropForeign(['old_supervisor_id']);
            $table->dropColumn('old_supervisor_id');
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn('supervisor_id');

        });
    }
};
