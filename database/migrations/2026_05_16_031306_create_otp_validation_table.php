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
        Schema::create('otp_validations', function (Blueprint $table) {
            $table->id();
            $table->text('mobile_no');
            $table->string('ip_address')->nullable(); 
            $table->string('user_agent')->nullable(); 
            $table->smallInteger('verification_id');
            $table->foreign('verification_id','verification_id_fk')->references('id')->on('verification_codes')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_validations');
    }
};
