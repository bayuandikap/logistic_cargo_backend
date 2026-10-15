<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Filament\Resources\ShipmentResource\RelationManagers;
use App\Models\Shipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(
        \Filament\Forms\Form $form
    ): \Filament\Forms\Form {
        return $form->schema([
            \Filament\Forms\Components\Section::make('Informasi Pengiriman')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->disabled()
                        ->dehydrated(false),

                    \Filament\Forms\Components\Select::make('origin_branch_id')
                        ->label('Cabang Asal')
                        ->relationship('originBranch', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    \Filament\Forms\Components\Select::make('destination_branch_id')
                        ->label('Cabang Tujuan')
                        ->relationship('destinationBranch', 'name')
                        ->searchable()
                        ->preload()
                        ->different('origin_branch_id')
                        ->required(),

                    \Filament\Forms\Components\Select::make('service_type')
                        ->label('Jenis Layanan')
                        ->options([
                            'REGULAR_SEA' => 'Kargo Laut Reguler',
                            'EXPRESS_AIR' => 'Kargo Udara Ekspres',
                            'OVERLAND' => 'Darat',
                        ])
                        ->default('REGULAR_SEA')
                        ->required(),
                ])->columns(2),

            \Filament\Forms\Components\Section::make('Pengirim')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('sender_name')
                        ->label('Nama Pengirim')->required(),
                    \Filament\Forms\Components\TextInput::make('sender_phone')
                        ->label('Telepon Pengirim')->required(),
                    \Filament\Forms\Components\Textarea::make('sender_address')
                        ->label('Alamat Pengirim')->required()->columnSpanFull(),
                ])->columns(2),

            \Filament\Forms\Components\Section::make('Penerima')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('recipient_name')
                        ->label('Nama Penerima')->required(),
                    \Filament\Forms\Components\TextInput::make('recipient_phone')
                        ->label('Telepon Penerima')->required(),
                    \Filament\Forms\Components\Textarea::make('recipient_address')
                        ->label('Alamat Penerima')->required()->columnSpanFull(),
                ])->columns(2),

            \Filament\Forms\Components\Section::make('Berat dan Tarif')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('chargeable_weight')
                        ->label('Berat Aktual (kg)')
                        ->numeric()->minValue(0.01)->required(),

                    \Filament\Forms\Components\TextInput::make('volume_weight')
                        ->label('Berat Volume (kg)')
                        ->numeric()->minValue(0)->default(0)->required(),

                    \Filament\Forms\Components\TextInput::make('total_amount')
                        ->label('Total Biaya (Rp)')
                        ->numeric()->minValue(0)->default(0)->required(),

                    \Filament\Forms\Components\DatePicker::make('estimated_arrival')
                        ->label('Estimasi Tiba'),
                ])->columns(2),
        ]);
    }



    public static function table(
        \Filament\Tables\Table $table
    ): \Filament\Tables\Table {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Nomor Resi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                \Filament\Tables\Columns\TextColumn::make('sender_name')
                    ->label('Pengirim')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('originBranch.name')
                    ->label('Asal'),

                \Filament\Tables\Columns\TextColumn::make('destinationBranch.name')
                    ->label('Tujuan'),

                \Filament\Tables\Columns\TextColumn::make('current_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'REGISTERED' => 'gray',
                        'IN_MARITIME_TRANSIT' => 'info',
                        'DELIVERED' => 'success',
                        default => 'warning',
                    }),

                \Filament\Tables\Columns\TextColumn::make('total_amount')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }
}
