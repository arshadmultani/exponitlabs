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
        Schema::create('ar_creatives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('marker_image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->string('mind_file_path')->nullable();
            // Number of trackable feature points MindAR found in the marker image.
            // Surfaced as a Good/Weak "trackability" badge so the admin knows
            // whether an image will track well before printing it.
            $table->unsignedInteger('tracking_score')->nullable();
            $table->string('status')->default('draft'); // draft | published
            $table->string('play_mode')->default('loop'); // loop | once
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ar_creatives');
    }
};
