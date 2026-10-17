<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use App\Models\Tariff;
use App\Services\FreightCalculator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateShipment extends CreateRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Check tariff before attempting freight calculation.
        $tariffExists = Tariff::query()
            ->where('origin_branch_id', $data['origin_branch_id'])
            ->where('destination_branch_id', $data['destination_branch_id'])
            ->where('service_type', $data['service_type'])
            ->where('is_active', true)
            ->exists();

        if (! $tariffExists) {
            throw ValidationException::withMessages([
                'data.origin_branch_id' =>
                'Tarif untuk rute dan jenis layanan ini belum tersedia. '
                    . 'Silakan buat atau aktifkan tarif terlebih dahulu.',
            ]);
        }

        try {
            return DB::transaction(function () use ($data): Model {
                $quote = app(FreightCalculator::class)->calculate(
                    originBranchId: (int) $data['origin_branch_id'],
                    destinationBranchId: (int) $data['destination_branch_id'],
                    serviceType: $data['service_type'],
                    actualWeightKg: (float) $data['actual_weight_kg'],
                    lengthCm: (float) $data['length_cm'],
                    widthCm: (float) $data['width_cm'],
                    heightCm: (float) $data['height_cm'],
                );

                $data['tariff_id'] = $quote['tariff_id'];
                $data['actual_weight_kg'] = $quote['actual_weight_kg'];
                $data['volume_weight'] = $quote['volume_weight_kg'];
                $data['chargeable_weight'] = $quote['chargeable_weight_kg'];
                $data['total_amount'] = $quote['total_amount'];

                $data['base_fee_applied'] = $quote['base_fee'];
                $data['rate_per_kg_applied'] = $quote['rate_per_kg'];
                $data['weight_fee'] = $quote['weight_fee'];
                $data['volumetric_divisor_applied'] =
                    $quote['volumetric_divisor'];

                return static::getModel()::create($data);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('Pengiriman gagal dibuat')
                ->body(
                    'Perhitungan ongkir gagal. Periksa konfigurasi tarif '
                        . 'dan data pengiriman, lalu coba kembali.'
                )
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
