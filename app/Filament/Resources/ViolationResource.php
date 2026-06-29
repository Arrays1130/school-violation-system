<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ViolationResource\Pages;
use App\Filament\Resources\ViolationResource\RelationManagers;
use App\Models\Violation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ViolationResource extends Resource
{
    protected static ?string $model = Violation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\Select::make('severity')
                    ->options([
                        'Minor' => 'Minor',
                        'Major' => 'Major',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('default_description')
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Sanctions')
                    ->schema([
                        Forms\Components\TextInput::make('first_offense')
                            ->label('1st Offense Sanction'),
                        Forms\Components\TextInput::make('second_offense')
                            ->label('2nd Offense Sanction'),
                        Forms\Components\TextInput::make('third_offense')
                            ->label('3rd Offense Sanction'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListViolations::route('/'),
            'create' => Pages\CreateViolation::route('/create'),
            'edit' => Pages\EditViolation::route('/{record}/edit'),
        ];
    }
}
