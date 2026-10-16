<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('length_cm', 10, 2)->nullable();
            $table->decimal('width_cm', 10, 2)->nullable();
            $table->decimal('height_cm', 10, 2)->nullable();

            $table->decimal('actual_weight_kg', 10, 2)->nullable();

            $table->foreignId('tariff_id')
                ->nullable()
                ->constrained('tariffs')
                ->nullOnDelete();

            $table->decimal('base_fee_applied', 12, 2)->nullable();
            $table->decimal('rate_per_kg_applied', 12, 2)->nullable();
            $table->decimal('weight_fee', 12, 2)->nullable();
            $table->unsignedInteger('volumetric_divisor_applied')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['tariff_id']);

            $table->dropColumn([
                'length_cm',
                'width_cm',
                'height_cm',
                'actual_weight_kg',
                'tariff_id',
                'base_fee_applied',
                'rate_per_kg_applied',
                'weight_fee',
                'volumetric_divisor_applied',
            ]);
        });
    }
};
