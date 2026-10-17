<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use App\Services\FreightCalculator;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditShipment extends EditRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(
                    fn(): bool =>
                    auth()->user()?->can('delete shipments') ?? false
                ),
        ];
    }

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        return DB::transaction(function () use ($record, $data): Model {
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

            $record->update($data);

            return $record;
        });
    }
}
