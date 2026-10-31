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
        Schema::table('dcr_products', function (Blueprint $table) {
            $table->decimal('unit_cost_snapshot', 10, 2)->nullable()->after('quantity');
        });

        Schema::table('dcr_promotional_inputs', function (Blueprint $table) {
            $table->decimal('unit_cost_snapshot', 10, 2)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dcr_products', function (Blueprint $table) {
            $table->dropColumn('unit_cost_snapshot');
        });

        Schema::table('dcr_promotional_inputs', function (Blueprint $table) {
            $table->dropColumn('unit_cost_snapshot');
        });
    }
};
