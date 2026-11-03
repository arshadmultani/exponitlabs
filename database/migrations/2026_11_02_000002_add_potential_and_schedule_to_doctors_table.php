<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->foreignId('doctor_potential_id')
                ->nullable()
                ->after('area_id')
                ->constrained('doctor_potentials')
                ->nullOnDelete();

            $table->json('visiting_days')->nullable()->after('clinic_name');
            $table->json('visiting_shifts')->nullable()->after('visiting_days');

            $table->index(['status', 'doctor_potential_id', 'area_id'], 'idx_doctors_cadence_lookup');
            $table->index('doctor_potential_id');
        });

        // Seed default commercial tiers and backfill existing doctors automatically on migrate
        $now = now();
        DB::table('doctor_potentials')->insertOrIgnore([
            ['name' => 'Core', 'code' => 'core', 'color' => 'success', 'sort_order' => 1, 'target_visits_per_month' => 4, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Serious', 'code' => 'serious', 'color' => 'info', 'sort_order' => 2, 'target_visits_per_month' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nascent', 'code' => 'nascent', 'color' => 'warning', 'sort_order' => 3, 'target_visits_per_month' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Unresponsive', 'code' => 'unresponsive', 'color' => 'gray', 'sort_order' => 4, 'target_visits_per_month' => 0, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $nascentId = DB::table('doctor_potentials')->where('code', 'nascent')->value('id');
        if ($nascentId) {
            DB::table('doctors')->whereNull('doctor_potential_id')->update([
                'doctor_potential_id' => $nascentId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('idx_doctors_cadence_lookup');
            $table->dropIndex(['doctor_potential_id']);
            $table->dropConstrainedForeignId('doctor_potential_id');
            $table->dropColumn(['visiting_days', 'visiting_shifts']);
        });
    }
};
