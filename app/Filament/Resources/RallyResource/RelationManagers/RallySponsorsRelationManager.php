<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\Sponsor;
use Filament\Notifications\Notification;
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
use Illuminate\Support\Collection;

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
                TextColumn::make('type')
                    ->searchable()
                    ->grow()
                    ->badge()
                    ->label('Sponsorship Type')
                    ->color(Color::Sky),
            ])
            ->striped()
            ->defaultSort('name')
            ->searchPlaceholder('Search (By Name)')
            ->headerActions([
                AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('sponsor_id')
                            ->label('Select Sponsor')
                            ->options(function ($state) {
                                $attachedSponsors = $this->ownerRecord->rallySponsors->pluck('id');
                                return Sponsor::whereNotIn('id', $attachedSponsors)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Only sponsors not yet added to this rally are listed.'),
                        Forms\Components\TextInput::make('type')
                            ->label('Sponsorship Type')
                            ->required()
                            ->placeholder('Tire supplier')
                            ->maxLength(255),
                    ])
                    ->action(function ($data) {
                        $rally = $this->ownerRecord;
                        $sponsor = Sponsor::find($data['sponsor_id']);
                        $rally->rallySponsors()->attach($data['sponsor_id'], ['type' => $data['type']]);

                        Notification::make()
                            ->title("Sponsor <strong>\"{$sponsor->name}\"</strong> Added")
                            ->success()
                            ->send();
                    })
                    ->label('Add Sponsor')
                    ->color('primary')
                    ->modalHeading('Add a Sponsor to This Rally')
                    ->modalSubmitActionLabel('Add'),
            ])
            ->actions([
                EditAction::make()
                    ->color(Color::Sky),
                DetachAction::make()
                    ->label('Remove Sponsor')
                    ->action(function ($record, $livewire) {
                        $sponsor = $record->name;
                        $livewire->getOwnerRecord()->rallySponsors()->detach($record->id);

                        Notification::make()
                            ->title("Sponsor <strong>\"{$sponsor}\"</strong> Removed")
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Remove Selected Sponsors')
                    ->action(function (Collection $records, RelationManager $livewire) {
                        $count = $records->count();
                        foreach ($records as $record) {
                            $livewire->getOwnerRecord()->rallySponsors()->detach($record->id);
                        }

                        Notification::make()
                            ->title("{$count} Sponsors Removed")
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
