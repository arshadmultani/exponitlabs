<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();

            // Inputs
            $table->decimal('retailer_margin_percent', 5, 2)->default(20);
            $table->decimal('stockist_margin_percent', 5, 2)->default(10);
            $table->unsignedInteger('scheme_paid_units')->default(10);
            $table->unsignedInteger('scheme_free_units')->default(0);
            $table->decimal('gst_percent', 5, 2)->default(5);
            $table->string('supply_type')->default('intra_state'); // intra_state | inter_state
            $table->decimal('unit_cost', 10, 2)->nullable();

            // Computed snapshot (MRP captured from the product at save time)
            $table->decimal('mrp_snapshot', 10, 2)->nullable();
            $table->decimal('ptr', 12, 4)->nullable();
            $table->decimal('pts', 12, 4)->nullable();
            $table->decimal('effective_pts', 12, 4)->nullable();
            $table->decimal('retailer_margin_per_unit', 12, 4)->nullable();
            $table->decimal('stockist_margin_per_unit', 12, 4)->nullable();
            $table->decimal('stockist_margin_percent_actual', 6, 2)->nullable();
            $table->decimal('taxable_value_pts', 12, 4)->nullable();
            $table->decimal('gst_amount_pts', 12, 4)->nullable();
            $table->decimal('invoice_total', 12, 4)->nullable();
            $table->decimal('profit_per_unit', 12, 4)->nullable();
            $table->decimal('profit_margin_percent', 6, 2)->nullable();
            $table->decimal('markup_percent', 6, 2)->nullable();
            $table->decimal('mrp_to_cost_ratio', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pricings');
    }
};
