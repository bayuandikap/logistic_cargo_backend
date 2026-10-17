<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TariffResource\Pages;
use App\Models\Branch;
use App\Models\Tariff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TariffResource extends Resource
{
    protected static ?string $model = Tariff::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Manajemen Pengiriman';

    protected static ?string $navigationLabel = 'Tarif Pengiriman';

    protected static ?string $modelLabel = 'Tarif Pengiriman';

    protected static ?string $pluralModelLabel = 'Tarif Pengiriman';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view tariffs') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create tariffs') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit tariffs') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete tariffs') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete tariffs') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Rute Pengiriman')
                ->schema([
                    Forms\Components\Select::make('origin_branch_id')
                        ->label('Cabang Asal')
                        ->options(
                            fn() => Branch::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('destination_branch_id')
                        ->label('Cabang Tujuan')
                        ->options(
                            fn() => Branch::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->different('origin_branch_id')
                        ->required()
                        ->native(false),

                    Forms\Components\Select::make('service_type')
                        ->label('Jenis Layanan')
                        ->options([
                            'REGULAR_SEA' => 'Kargo Laut Reguler',
                            'EXPRESS_AIR' => 'Kargo Udara Ekspres',
                            'OVERLAND' => 'Kargo Darat',
                        ])
                        ->default('REGULAR_SEA')
                        ->required()
                        ->native(false),
                ])
                ->columns(2),

            Forms\Components\Section::make('Komponen Biaya')
                ->schema([
                    Forms\Components\TextInput::make('base_fee')
                        ->label('Biaya Dasar (Rp)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('rate_per_kg')
                        ->label('Tarif per Kg (Rp)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('volumetric_divisor')
                        ->label('Pembagi Berat Volume')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->default(5000)
                        ->required()
                        ->helperText(
                            'Default 5000: panjang × lebar × tinggi (cm) ÷ 5000.'
                        ),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Tarif Aktif')
                        ->default(true)
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('originBranch.name')
                    ->label('Cabang Asal')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('destinationBranch.name')
                    ->label('Cabang Tujuan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Layanan')
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            'REGULAR_SEA' => 'Kargo Laut Reguler',
                            'EXPRESS_AIR' => 'Kargo Udara Ekspres',
                            'OVERLAND' => 'Kargo Darat',
                            default => $state,
                        }
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_fee')
                    ->label('Biaya Dasar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate_per_kg')
                    ->label('Tarif/Kg')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('volumetric_divisor')
                    ->label('Pembagi Volume')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Tarif')
                    ->placeholder('Semua tarif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn(Tariff $record): bool =>
                        auth()->user()?->can('edit tariffs') ?? false
                    ),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(
                        fn(Tariff $record): bool =>
                        auth()->user()?->can('delete tariffs') ?? false
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->visible(
                            fn(): bool =>
                            auth()->user()?->can('delete tariffs') ?? false
                        ),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTariffs::route('/'),
            'create' => Pages\CreateTariff::route('/create'),
            'edit' => Pages\EditTariff::route('/{record}/edit'),
        ];
    }
}
