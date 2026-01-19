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
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('asset_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('comment_replies', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('company_content_management', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('complaints', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('holidays', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('notices', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('office_times', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('promotions', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('resignations', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('salary_revise_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('supports', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('tadas', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('team_meetings', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('terminations', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('time_leaves', function (Blueprint $table) {
            $table->unsignedBigInteger('referred_by')->nullable()->change();
        });

        Schema::table('trainers', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('trainings', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('warnings', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        Schema::table('task_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
