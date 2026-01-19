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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('email');
            $table->string('address')->nullable()->after('password');
            $table->string('avatar')->nullable()->after('address');
            $table->date('dob')->nullable()->after('avatar');
            $table->string('gender')->default('male')->after('dob');
            $table->double('phone')->nullable()->after('gender');
            $table->string('status')->default('verified')->after('phone');
            $table->boolean('is_active')->default('0')->after('status');
            $table->boolean('online_status')->default('0')->after('is_active');
            $table->bigInteger('role_id')->unsigned()->nullable()->after('online_status');
            $table->integer('leave_allocated')->nullable()->after('role_id');
            $table->string('employment_type')->default('temporary')->after('leave_allocated');
            $table->string('user_type')->default('field')->after('employment_type');

            $table->date('joining_date')->nullable()->after('user_type');
            $table->boolean('workspace_type')->default(1)->after('joining_date');

            $table->text('uuid')->nullable()->after('workspace_type');
            $table->longText('fcm_token')->nullable()->after('uuid');
            $table->string('device_type')->nullable()->after('fcm_token');
            $table->boolean('logout_status')->default(0)->after('device_type');
            $table->longText('remarks')->nullable()->after('logout_status');

            $table->bigInteger('company_id')->unsigned()->nullable()->after('remarks');
            $table->bigInteger('branch_id')->unsigned()->nullable()->after('company_id');
            $table->bigInteger('department_id')->unsigned()->nullable()->after('branch_id');
            $table->bigInteger('post_id')->unsigned()->nullable()->after('department_id');
            $table->bigInteger('supervisor_id')->unsigned()->nullable()->after('post_id');
            $table->bigInteger('office_time_id')->unsigned()->nullable()->after('supervisor_id');
            $table->bigInteger('created_by')->unsigned()->nullable()->after('remember_token');
            $table->bigInteger('updated_by')->unsigned()->nullable()->after('created_by');
            $table->bigInteger('deleted_by')->unsigned()->nullable()->after('updated_by');
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('supervisor_id')->references('id')->on('users');
            $table->foreign('office_time_id')->references('id')->on('office_times');
            $table->foreign('role_id')->references('id')->on('roles');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
