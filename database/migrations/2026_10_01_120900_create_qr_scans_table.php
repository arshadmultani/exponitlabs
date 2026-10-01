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
        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained('qr_codes')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('country_name')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_type')->nullable()->index();
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->string('platform')->nullable()->index();
            $table->string('platform_version')->nullable();
            $table->string('browser')->nullable()->index();
            $table->string('browser_version')->nullable();
            $table->boolean('is_unique')->default(false)->index();
            $table->text('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('scanned_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_scans');
    }
};
