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
            $table->boolean('no_income_tax')->nullable()->after('no_financial_assistance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pension.beneficiary_self_declarations', function (Blueprint $table) {
            $table->dropColumn('no_income_tax');
        });
    }
};
