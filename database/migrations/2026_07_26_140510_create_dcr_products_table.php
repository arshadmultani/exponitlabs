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
        // created to record DCR visit sample giving
        Schema::create('dcr_products', function (Blueprint $table) {

            $table->id();

            $table->foreignId('dcr_id')->constrained('d_c_r_s')->cascadeOnDelete();

            $table->foreignId('product_id')->constrained('products');

            $table->unsignedSmallInteger('quantity')->default(0);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dcr_products');
    }
};
