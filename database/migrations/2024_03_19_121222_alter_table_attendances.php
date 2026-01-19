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
        Schema::table('attendances', function (Blueprint $table) {
            $table->double('worked_hour',10,2)->nullable()->comment('in minutes');
            $table->double('overtime',10,2)->nullable()->comment('in minutes');
            $table->double('undertime',10,2)->nullable()->comment('in minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('worked_hour');
            $table->dropColumn('overtime');
            $table->dropColumn('undertime');
        });
    }
};
