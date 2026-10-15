<?php

namespace App\Filament\Resources\TrackingCheckpointResource\Pages;

use App\Filament\Resources\TrackingCheckpointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrackingCheckpoint extends EditRecord
{
    protected static string $resource = TrackingCheckpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
