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
        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_type_id')->nullable()->change();
            $table->string('title')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_type_id')->nullable(false)->change();
            $table->dropColumn('title');
        });
    }
};
