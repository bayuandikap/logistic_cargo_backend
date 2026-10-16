<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingCheckpoint extends Model
{
    protected $fillable = [
        'shipment_id',
        'branch_id',
        'status',
        'location_label',
        'description',
        'event_timestamp',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_timestamp' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
