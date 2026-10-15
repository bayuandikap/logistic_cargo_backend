<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number',
        'sender_name',
        'sender_phone',
        'sender_address',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'origin_branch_id',
        'destination_branch_id',
        'service_type',
        'chargeable_weight',
        'volume_weight',
        'total_amount',
        'payment_status',
        'current_status',
        'estimated_arrival',
    ];

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            if (blank($shipment->tracking_number)) {
                $shipment->tracking_number = DB::transaction(
                    function () {
                        $prefix = 'AKA-' . now()->format('Ym') . '-';

                        do {
                            $number = $prefix . Str::upper(
                                Str::random(4)
                            );
                        } while (
                            Shipment::where('tracking_number', $number)->exists()
                        );

                        return $number;
                    }
                );
            }

            if (blank($shipment->current_status)) {
                $shipment->current_status = 'REGISTERED';
            }

            if (blank($shipment->payment_status)) {
                $shipment->payment_status = 'UNPAID';
            }
        });
    }

    protected function casts(): array
    {
        return [
            'chargeable_weight' => 'decimal:2',
            'volume_weight' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'estimated_arrival' => 'date',
        ];
    }

    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(TrackingCheckpoint::class);
    }
}
