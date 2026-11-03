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
        Schema::create('festivals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('category', 50)->default('cultural_and_national')->index();
            $table->json('target_specialties')->nullable();
            $table->unsignedTinyInteger('days_before')->default(3);
            $table->unsignedTinyInteger('days_after')->default(1);
            $table->text('greeting_message_template')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('festival_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained('festivals')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->date('date');
            $table->boolean('is_manually_adjusted')->default(false);
            $table->foreignId('adjusted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('adjustment_notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['festival_id', 'date'], 'idx_festival_date_unique');
            $table->index(['date', 'festival_id'], 'idx_festival_lookup');
            $table->index(['year', 'festival_id'], 'idx_festival_year_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festival_dates');
        Schema::dropIfExists('festivals');
    }
};
