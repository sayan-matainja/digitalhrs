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
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('award_type_id')->constrained('award_types')->onDelete('cascade');
            $table->string('gift_item');
            $table->string('award_base')->nullable();
            $table->date('awarded_date');
            $table->string('awarded_by')->nullable();
            $table->boolean('status')->default(0);
            $table->longText('award_description')->nullable();
            $table->longText('gift_description')->nullable();
            $table->string('attachment')->nullable();
            $table->string('reward_code')->nullable();
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
        Schema::dropIfExists('awards');
    }
};
