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
        Schema::table('id_card_settings', function (Blueprint $table) {
            $table->string('front_logo')->nullable();
            $table->string('back_logo')->nullable();
            $table->string('signature_image')->nullable();
            $table->text('footer_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('id_card_settings', function (Blueprint $table) {
            $table->dropColumn('front_logo');
            $table->dropColumn('back_logo');
            $table->dropColumn('signature_image');
            $table->dropColumn('footer_text');
        });
    }
};
