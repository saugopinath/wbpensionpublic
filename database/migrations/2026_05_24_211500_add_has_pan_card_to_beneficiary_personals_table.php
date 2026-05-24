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
            $table->smallInteger('has_pan_card')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pension.beneficiary_personals', function (Blueprint $table) {
            $table->dropColumn('has_pan_card');
        });
    }
};
