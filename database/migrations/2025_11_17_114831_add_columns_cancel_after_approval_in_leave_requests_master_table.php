<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->boolean('cancel_request')->default(false);
            $table->timestamp('cancellation_approved_at')->nullable();
            $table->unsignedBigInteger('cancellation_approved_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreign('cancellation_approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->dropForeign(['cancellation_approved_by']);
            $table->dropColumn([
                'cancel_request',
                'cancellation_approved_at',
                'cancellation_approved_by',
                'cancellation_reason'
            ]);
        });
    }
};
