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
        // created to record DCR visit input giving
        Schema::create('dcr_promotional_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dcr_id')->constrained('d_c_r_s')->cascadeOnDelete();
            $table->foreignId('promotional_input_id')->constrained('promotional_inputs')->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(0);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dcr_promotional_inputs');
    }
};
