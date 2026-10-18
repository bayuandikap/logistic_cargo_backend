<?php

namespace App\Filament\Resources\TrackingCheckpointResource\Pages;

use App\Filament\Resources\TrackingCheckpointResource;
use App\Models\Shipment;
use App\Models\TrackingCheckpoint;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditTrackingCheckpoint extends EditRecord
{
    protected static string $resource = TrackingCheckpointResource::class;

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        return DB::transaction(function () use ($record, $data): Model {
            $shipmentId = (int) $record->shipment_id;

            $updatedRecord = $record->update($data)
                ? $record->refresh()
                : $record;

            $this->syncShipmentStatus($shipmentId);

            return $updatedRecord;
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (TrackingCheckpoint $record): void {
                    $this->syncShipmentStatus(
                        (int) $record->shipment_id
                    );
                }),
        ];
    }

    protected function syncShipmentStatus(int $shipmentId): void
    {
        DB::transaction(function () use ($shipmentId): void {
            $shipment = Shipment::query()
                ->lockForUpdate()
                ->findOrFail($shipmentId);

            $latestCheckpoint = TrackingCheckpoint::query()
                ->where('shipment_id', $shipmentId)
                ->orderByDesc('event_timestamp')
                ->orderByDesc('id')
                ->first();

            $shipment->current_status = $latestCheckpoint
                ? $latestCheckpoint->status
                : 'REGISTERED';

            $shipment->save();
        });
    }
}
