<?php

namespace App\Filament\Resources\TrackingCheckpointResource\Pages;

use App\Filament\Resources\TrackingCheckpointResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateTrackingCheckpoint extends CreateRecord
{
    protected static string $resource = TrackingCheckpointResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $checkpoint = static::getModel()::create($data);

            $shipment = $checkpoint->shipment()
                ->lockForUpdate()
                ->firstOrFail();

            // Update status only when this is the latest checkpoint.
            $latestCheckpoint = $shipment->checkpoints()
                ->orderByDesc('event_timestamp')
                ->orderByDesc('id')
                ->first();

            if ($latestCheckpoint && $latestCheckpoint->is($checkpoint)) {
                $shipment->current_status = $checkpoint->status;
                $shipment->save();
            }

            return $checkpoint;
        });
    }
}
