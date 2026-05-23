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
        Schema::table('pension.beneficiary_self_declarations', function (Blueprint $table) {
            $table->boolean('no_financial_assistance')->nullable()->after('earn_monthly_remuneration');
            $table->boolean('aadhaar_consent')->nullable()->after('info_genuine_decl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pension.beneficiary_self_declarations', function (Blueprint $table) {
            $table->dropColumn(['no_financial_assistance', 'aadhaar_consent']);
        });
    }
};
