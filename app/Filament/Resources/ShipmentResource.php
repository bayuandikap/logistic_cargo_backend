<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Models\Shipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Pengiriman';

    protected static ?string $navigationLabel = 'Pengiriman';

    protected static ?string $modelLabel = 'Pengiriman';

    protected static ?string $pluralModelLabel = 'Pengiriman';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view shipments') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create shipments') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit shipments') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete shipments') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete shipments') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pengiriman')
                ->schema([
                    Forms\Components\TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\Select::make('origin_branch_id')
                        ->label('Cabang Asal')
                        ->relationship('originBranch', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('destination_branch_id')
                        ->label('Cabang Tujuan')
                        ->relationship('destinationBranch', 'name')
                        ->searchable()
                        ->preload()
                        ->different('origin_branch_id')
                        ->required(),

                    Forms\Components\Select::make('service_type')
                        ->label('Jenis Layanan')
                        ->options([
                            'REGULAR_SEA' => 'Kargo Laut Reguler',
                            'EXPRESS_AIR' => 'Kargo Udara Ekspres',
                            'OVERLAND' => 'Darat',
                        ])
                        ->default('REGULAR_SEA')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Pengirim')
                ->schema([
                    Forms\Components\TextInput::make('sender_name')
                        ->label('Nama Pengirim')
                        ->required(),

                    Forms\Components\TextInput::make('sender_phone')
                        ->label('Telepon Pengirim')
                        ->required(),

                    Forms\Components\Textarea::make('sender_address')
                        ->label('Alamat Pengirim')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Penerima')
                ->schema([
                    Forms\Components\TextInput::make('recipient_name')
                        ->label('Nama Penerima')
                        ->required(),

                    Forms\Components\TextInput::make('recipient_phone')
                        ->label('Telepon Penerima')
                        ->required(),

                    Forms\Components\Textarea::make('recipient_address')
                        ->label('Alamat Penerima')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Berat dan Tarif')
                ->schema([
                    Forms\Components\TextInput::make('actual_weight_kg')
                        ->label('Berat Aktual (kg)')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->required(),

                    Forms\Components\TextInput::make('length_cm')
                        ->label('Panjang (cm)')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->required(),

                    Forms\Components\TextInput::make('width_cm')
                        ->label('Lebar (cm)')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->required(),

                    Forms\Components\TextInput::make('height_cm')
                        ->label('Tinggi (cm)')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->required(),

                    Forms\Components\TextInput::make('chargeable_weight')
                        ->label('Berat yang Ditagihkan (kg)')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('volume_weight')
                        ->label('Berat Volume (kg)')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total Biaya (Rp)')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\DatePicker::make('estimated_arrival')
                        ->label('Estimasi Tiba'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Nomor Resi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('sender_name')
                    ->label('Pengirim')
                    ->searchable(),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable(),

                Tables\Columns\TextColumn::make('originBranch.name')
                    ->label('Asal'),

                Tables\Columns\TextColumn::make('destinationBranch.name')
                    ->label('Tujuan'),

                Tables\Columns\TextColumn::make('current_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'REGISTERED' => 'gray',
                        'IN_MARITIME_TRANSIT' => 'info',
                        'DELIVERED' => 'success',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(
                        fn(Shipment $record): bool =>
                        auth()->user()?->can('edit shipments') ?? false
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }
}
