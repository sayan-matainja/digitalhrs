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
        Schema::table('employee_accounts', function (Blueprint $table) {
           $table->string('salary_cycle')->default('monthly')->after('salary');
           $table->unsignedBigInteger('salary_group_id')->nullable()->after('salary_cycle');
           $table->boolean('allow_generate_payroll')->default(false)->after('salary_group_id');

           $table->foreign('salary_group_id')->references('id')->on('salary_groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_accounts', function (Blueprint $table) {
            //
        });
    }
};
