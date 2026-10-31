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
        Schema::table('promotional_inputs', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0.00)->after('name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sample_cost', 10, 2)->nullable()->after('unit_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotional_inputs', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sample_cost');
        });
    }
};
