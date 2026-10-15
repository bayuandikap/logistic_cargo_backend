<?php

namespace App\Filament\Resources\TrackingCheckpointResource\Pages;

use App\Filament\Resources\TrackingCheckpointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrackingCheckpoints extends ListRecords
{
    protected static string $resource = TrackingCheckpointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
