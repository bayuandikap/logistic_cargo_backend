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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 30)->unique();

            $table->string('sender_name', 150);
            $table->string('sender_phone', 25);
            $table->text('sender_address');

            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 25);
            $table->text('recipient_address');

            $table->foreignId('origin_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('destination_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->string('service_type', 50)->default('REGULAR_SEA');
            $table->decimal('chargeable_weight', 10, 2);
            $table->decimal('volume_weight', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            $table->string('payment_status', 30)->default('UNPAID');
            $table->string('current_status', 50)->default('REGISTERED');
            $table->date('estimated_arrival')->nullable();

            $table->timestamps();

            $table->index('current_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
