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
        Schema::table('tracking_checkpoints', function (Blueprint $table) {
            if (! Schema::hasColumn('tracking_checkpoints', 'branch_id')) {
                Schema::table('tracking_checkpoints', function (Blueprint $table) {
                    $table->foreignId('branch_id')
                        ->nullable()
                        ->constrained('branches')
                        ->nullOnDelete();
                });
            }

            if (! Schema::hasColumn('tracking_checkpoints', 'location_label')) {
                $table->string('location_label', 150)->nullable();
            }

            if (! Schema::hasColumn('tracking_checkpoints', 'event_timestamp')) {
                $table->timestampTz('event_timestamp')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_checkpoints', function (Blueprint $table) {
            //
        });
    }
};
