<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('origin_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('destination_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->string('service_type')->default('REGULAR_SEA');
            $table->decimal('base_fee', 12, 2)->default(0);
            $table->decimal('rate_per_kg', 12, 2)->default(0);
            $table->unsignedInteger('volumetric_divisor')->default(5000);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'origin_branch_id',
                'destination_branch_id',
                'service_type',
            ], 'tariffs_route_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
