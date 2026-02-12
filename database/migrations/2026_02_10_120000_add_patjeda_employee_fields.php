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
        // Add new fields to users table
        Schema::table('users', function (Blueprint $table) {
            // Personal Details Section - Split name field
            $table->string('surname', 100)->nullable()->after('name');
            $table->string('first_name', 100)->nullable()->after('surname');
            $table->string('middle_name', 100)->nullable()->after('first_name');
            
            // Personal Details Section - NIN field
            $table->string('nin', 20)->nullable()->after('phone');
            
            // Company Details Section - New fields after post_id
            $table->string('grade_level', 50)->nullable()->after('post_id');
            $table->string('tax_id', 50)->nullable()->after('grade_level');
            $table->string('sbu_code', 50)->nullable()->after('tax_id');
            $table->string('rsa_no', 50)->nullable()->after('sbu_code');
            $table->string('hmo_id', 50)->nullable()->after('rsa_no');
        });

        // Add BVN field to employee_accounts table
        Schema::table('employee_accounts', function (Blueprint $table) {
            $table->string('bvn', 11)->nullable()->after('bank_account_type');
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
            $table->dropColumn([
                'surname',
                'first_name', 
                'middle_name',
                'nin',
                'grade_level',
                'tax_id',
                'sbu_code',
                'rsa_no',
                'hmo_id'
            ]);
        });

        Schema::table('employee_accounts', function (Blueprint $table) {
            $table->dropColumn('bvn');
        });
    }
};
