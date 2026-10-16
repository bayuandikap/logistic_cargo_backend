<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackingCheckpointResource\Pages;
use App\Filament\Resources\TrackingCheckpointResource\RelationManagers;
use App\Models\TrackingCheckpoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class TrackingCheckpointResource extends Resource
{
    protected static ?string $model = TrackingCheckpoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view shipments') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage tracking') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage tracking') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(
        \Filament\Forms\Form $form
    ): \Filament\Forms\Form {
        return $form->schema([
            \Filament\Forms\Components\Select::make('shipment_id')
                ->label('Nomor Resi')
                ->relationship('shipment', 'tracking_number')
                ->searchable()
                ->preload()
                ->required(),

            \Filament\Forms\Components\Select::make('branch_id')
                ->label('Cabang / Lokasi')
                ->relationship('branch', 'name')
                ->searchable()
                ->preload(),

            \Filament\Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'REGISTERED' => 'Barang Diterima',
                    'IN_TRANSIT' => 'Dalam Perjalanan',
                    'IN_MARITIME_TRANSIT' => 'Dalam Pelayaran Laut',
                    'ARRIVED_AT_DESTINATION' => 'Tiba di Cabang Tujuan',
                    'DELIVERED' => 'Terkirim',
                    'ON_HOLD' => 'Ditahan',
                ])
                ->required(),

            \Filament\Forms\Components\TextInput::make('location_label')
                ->label('Lokasi')
                ->required()
                ->maxLength(150),

            \Filament\Forms\Components\Textarea::make('description')
                ->label('Keterangan')
                ->required()
                ->columnSpanFull(),

            \Filament\Forms\Components\DateTimePicker::make('event_timestamp')
                ->label('Waktu Kejadian')
                ->default(now())
                ->required(),
        ]);
    }

    public static function table(
        \Filament\Tables\Table $table
    ): \Filament\Tables\Table {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('shipment.tracking_number')
                    ->label('Nomor Resi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'REGISTERED' => 'gray',
                        'IN_TRANSIT' => 'info',
                        'IN_MARITIME_TRANSIT' => 'info',
                        'ARRIVED_AT_DESTINATION' => 'warning',
                        'DELIVERED' => 'success',
                        'ON_HOLD' => 'danger',
                        default => 'gray',
                    }),

                \Filament\Tables\Columns\TextColumn::make('location_label')
                    ->label('Lokasi')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50)
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('event_timestamp')
                    ->label('Waktu Kejadian')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->placeholder('-'),
            ])
            ->defaultSort('event_timestamp', 'desc')
            ->actions([
                \Filament\Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn(): bool =>
                        auth()->user()?->can('manage tracking') ?? false
                    ),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus Terpilih')
                    ->visible(
                        fn(): bool =>
                        auth()->user()?->hasRole('super_admin') ?? false
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrackingCheckpoints::route('/'),
            'create' => Pages\CreateTrackingCheckpoint::route('/create'),
            'edit' => Pages\EditTrackingCheckpoint::route('/{record}/edit'),
        ];
    }
}
