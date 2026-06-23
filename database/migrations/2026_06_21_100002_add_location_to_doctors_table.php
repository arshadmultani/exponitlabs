<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('town')->constrained()->nullOnDelete();
            $table->string('clinic_name')->nullable()->after('address');
            // Numeric coords (for proximity / tour-planning queries) derived from the pin.
            $table->decimal('latitude', 10, 7)->nullable()->after('clinic_name');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            // GeoJSON FeatureCollection (Frappe-style geolocation field, edited via the map).
            $table->json('location')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
            $table->dropColumn(['clinic_name', 'latitude', 'longitude', 'location']);
        });
    }
};
