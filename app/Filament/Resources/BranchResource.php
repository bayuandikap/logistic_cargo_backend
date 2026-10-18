<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Cabang';

    protected static ?string $modelLabel = 'Cabang';

    protected static ?string $pluralModelLabel = 'Cabang';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view branches') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create branches') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('edit branches') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('delete branches') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete branches') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')
                ->required()
                ->maxLength(10)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->required()
                ->maxLength(100),

            TextInput::make('city')
                ->required()
                ->maxLength(100),

            Textarea::make('address')
                ->required()
                ->columnSpanFull(),

            TextInput::make('phone')
                ->tel()
                ->maxLength(20),

            Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->actions([
                EditAction::make()
                    ->label('Ubah')
                    ->visible(
                        fn(Branch $record): bool =>
                        auth()->user()?->can('edit branches') ?? false
                    ),

                \Filament\Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->visible(
                        fn(Branch $record): bool =>
                        auth()->user()?->can('delete branches') ?? false
                    ),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->visible(
                            fn(): bool =>
                            auth()->user()?->can('delete branches') ?? false
                        ),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
