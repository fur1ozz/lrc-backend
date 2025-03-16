<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'classes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('class_name')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Class')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('class_name')->grow()->weight(FontWeight::Bold),
            ])
            ->striped()
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Class'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color(Color::Sky),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
