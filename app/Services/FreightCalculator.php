<?php

namespace App\Services;

use App\Models\Tariff;
use Illuminate\Validation\ValidationException;

class FreightCalculator
{
    public function calculate(
        int $originBranchId,
        int $destinationBranchId,
        string $serviceType,
        float $actualWeightKg,
        float $lengthCm,
        float $widthCm,
        float $heightCm,
    ): array {
        if ($originBranchId === $destinationBranchId) {
            throw ValidationException::withMessages([
                'destination_branch_id' =>
                    'Cabang asal dan tujuan tidak boleh sama.',
            ]);
        }

        foreach ([
            'actual_weight_kg' => $actualWeightKg,
            'length_cm' => $lengthCm,
            'width_cm' => $widthCm,
            'height_cm' => $heightCm,
        ] as $field => $value) {
            if (! is_finite($value) || $value <= 0) {
                throw ValidationException::withMessages([
                    $field => 'Nilai harus berupa angka lebih besar dari nol.',
                ]);
            }
        }

        $tariff = Tariff::query()
            ->where('origin_branch_id', $originBranchId)
            ->where('destination_branch_id', $destinationBranchId)
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->first();

        if (! $tariff) {
            throw ValidationException::withMessages([
                'service_type' =>
                    'Tarif aktif untuk rute dan layanan ini belum dikonfigurasi.',
            ]);
        }

        $baseFee = (float) $tariff->base_fee;
        $ratePerKg = (float) $tariff->rate_per_kg;
        $divisor = (int) $tariff->volumetric_divisor;

        if (
            ! is_finite($baseFee) ||
            ! is_finite($ratePerKg) ||
            $baseFee < 0 ||
            $ratePerKg < 0
        ) {
            throw ValidationException::withMessages([
                'service_type' => 'Konfigurasi tarif tidak valid.',
            ]);
        }

        if ($divisor <= 0) {
            throw ValidationException::withMessages([
                'service_type' => 'Pembagi berat volumetrik harus lebih besar dari nol.',
            ]);
        }

        $actualWeight = round($actualWeightKg, 2);

        $volumeWeight = round(
            ($lengthCm * $widthCm * $heightCm) / $divisor,
            2
        );

        $chargeableWeight = round(
            max($actualWeight, $volumeWeight),
            2
        );

        $weightFee = round($chargeableWeight * $ratePerKg, 2);

        $totalAmount = round($baseFee + $weightFee, 2);

        return [
            'tariff_id' => $tariff->id,
            'actual_weight_kg' => $actualWeight,
            'volume_weight_kg' => $volumeWeight,
            'chargeable_weight_kg' => $chargeableWeight,
            'volumetric_divisor' => $divisor,
            'base_fee' => round($baseFee, 2),
            'rate_per_kg' => round($ratePerKg, 2),
            'weight_fee' => $weightFee,
            'total_amount' => $totalAmount,
            'currency' => 'IDR',
        ];
    }
}
