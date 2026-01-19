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
        Schema::create('leave_requests_master', function (Blueprint $table) {
            $table->id();
            $table->integer('no_of_days');
            $table->bigInteger('leave_type_id')->unsigned();
            $table->dateTime('leave_requested_date');
            $table->dateTime('leave_from');
            $table->dateTime('leave_to');
            $table->string('status')->default('pending');
            $table->longText('reasons');
            $table->longText('admin_remark')->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('requested_by')->unsigned();
            $table->boolean('early_exit')->default(0);
            $table->bigInteger('request_updated_by')->unsigned()->nullable();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('request_updated_by')->references('id')->on('users');

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
        Schema::dropIfExists('leave_requests_master');
    }
};
