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
        Schema::table('d_c_r_s', function (Blueprint $table) {
            $table->index(['doctor_id', 'date'], 'idx_dcrs_doctor_date');
        });

        Schema::table('dcr_products', function (Blueprint $table) {
            $table->index(['dcr_id', 'product_id'], 'idx_dcr_products_composite');
        });

        Schema::table('dcr_promotional_inputs', function (Blueprint $table) {
            $table->index(['dcr_id', 'promotional_input_id'], 'idx_dcr_promos_composite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('d_c_r_s', function (Blueprint $table) {
            $table->dropIndex('idx_dcrs_doctor_date');
        });

        Schema::table('dcr_products', function (Blueprint $table) {
            $table->dropIndex('idx_dcr_products_composite');
        });

        Schema::table('dcr_promotional_inputs', function (Blueprint $table) {
            $table->dropIndex('idx_dcr_promos_composite');
        });
    }
};
