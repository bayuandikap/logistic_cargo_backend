<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(
        \Filament\Forms\Form $form
    ): \Filament\Forms\Form {
        return $form->schema([
            \Filament\Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(10)
                ->unique(ignoreRecord: true),

            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(100),

            \Filament\Forms\Components\TextInput::make('city')
                ->required()
                ->maxLength(100),

            \Filament\Forms\Components\Textarea::make('address')
                ->required()
                ->columnSpanFull(),

            \Filament\Forms\Components\TextInput::make('phone')
                ->tel()
                ->maxLength(20),

            \Filament\Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(
        \Filament\Tables\Table $table
    ): \Filament\Tables\Table {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('city')
                    ->searchable(),

                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
