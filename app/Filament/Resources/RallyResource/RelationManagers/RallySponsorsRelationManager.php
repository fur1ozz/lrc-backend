<?php
namespace App\Filament\Resources\RallyResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class RallySponsorsRelationManager extends RelationManager
{
    protected static string $relationship = 'rallySponsors';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('type')
                ->label('Sponsorship Type')
                ->required()
                ->placeholder('Tire supplier')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')->height(20)->alignRight(),
                TextColumn::make('name')->weight(FontWeight::SemiBold)->sortable()->searchable(),
                TextColumn::make('type')->weight(FontWeight::SemiBold)->searchable(),
            ])
            ->striped()
            ->defaultSort('name')
            ->searchPlaceholder('Search (By Name)')
            ->headerActions([
                AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('sponsor_id')
                        ->label('Select Sponsor')
                            ->options(\App\Models\Sponsor::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('type')
                        ->label('Sponsorship Type')
                            ->required()
                            ->placeholder('Tire supplier')
                            ->maxLength(255),
                    ])
                    ->action(function ($data) {
                        $rally = $this->record;
                        $rally->rallySponsors()->attach($data['sponsor_id'], ['type' => $data['type']]);
                    })

            ])
            ->actions([
                EditAction::make()
                    ->color(Color::Sky),
                DetachAction::make(),
            ]);
    }
}
