<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tariff extends Model
{
    protected $fillable = [
        'origin_branch_id',
        'destination_branch_id',
        'service_type',
        'base_fee',
        'rate_per_kg',
        'volumetric_divisor',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_fee' => 'decimal:2',
            'rate_per_kg' => 'decimal:2',
            'volumetric_divisor' => 'integer',
            'is_active' => 'boolean',
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
}
