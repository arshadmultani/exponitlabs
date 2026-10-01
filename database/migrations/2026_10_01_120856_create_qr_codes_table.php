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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('destination_url');
            $table->text('ios_url')->nullable();
            $table->text('android_url')->nullable();
            $table->text('fallback_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->dateTime('expires_at')->nullable();
            $table->unsignedInteger('max_scans')->nullable();
            $table->unsignedBigInteger('total_scans')->default(0);
            $table->unsignedBigInteger('unique_scans')->default(0);
            $table->dateTime('last_scanned_at')->nullable();
            $table->json('design')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
