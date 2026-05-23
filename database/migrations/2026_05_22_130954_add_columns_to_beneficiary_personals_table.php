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
        Schema::table('pension.beneficiary_personals', function (Blueprint $table) {
            $table->string('ration_card_no')->nullable();
            $table->string('epic_card_no')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('is_taxpayer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pension.beneficiary_personals', function (Blueprint $table) {
            $table->dropColumn(['ration_card_no', 'epic_card_no', 'pan_no', 'is_taxpayer']);
        });
    }
};
