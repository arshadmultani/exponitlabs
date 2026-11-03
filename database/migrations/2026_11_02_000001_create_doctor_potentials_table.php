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
        Schema::create('doctor_potentials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('code', 32)->unique();
            $table->string('color', 32)->default('gray');
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->unsignedSmallInteger('target_visits_per_month')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_potentials');
    }
};
