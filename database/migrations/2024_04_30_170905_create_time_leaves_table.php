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
        Schema::create('time_leaves', function (Blueprint $table) {
            $table->id();
            $table->date('issue_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default(\App\Enum\LeaveStatusEnum::pending->value);
            $table->longText('reasons');
            $table->longText('admin_remark')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->timestamps();
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_leaves');
    }
};
