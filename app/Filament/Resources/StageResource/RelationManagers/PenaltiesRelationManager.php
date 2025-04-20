<?php

namespace App\Filament\Resources\StageResource\RelationManagers;

use App\Models\Crew;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PenaltiesRelationManager extends RelationManager
{
    protected static string $relationship = 'penalties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('crew_id')
                    ->label('Crew')
                    ->options(function () {
                        $stage = $this->getOwnerRecord();
                        $rallyId = $stage->rally_id;

                        return Crew::where('rally_id', $rallyId)
                            ->with(['driver', 'coDriver'])
                            ->orderByRaw('is_historic ASC, crew_number_int ASC')
                            ->get()
                            ->mapWithKeys(fn ($crew) => [
                                $crew->id => "{$crew->driver?->name} {$crew->driver?->surname} / {$crew->coDriver?->name} {$crew->coDriver?->surname} (Car: {$crew->car}, No: {$crew->crew_number})"
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->placeholder('Select a crew')
                    ->visible(fn ($get) => empty($get('id'))),

                Forms\Components\TextInput::make('penalty_type')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('penalty_amount')
                    ->label('Penalty Time (min:sec.ms)')
                    ->placeholder('e.g. 1:23.45')
                    ->helperText('Enter time in format: min:sec.ms (e.g. 2:05.34)')
                    ->required()
                    ->mask('9:99.99')
                    ->suffix('min:sec.ms')
                    ->dehydrateStateUsing(function ($state) {
                        if (!preg_match('/^(\d+):(\d{2})\.(\d{2})$/', $state, $matches)) {
                            return null;
                        }

                        $minutes = (int)$matches[1];
                        $seconds = (int)$matches[2];
                        $hundredths = (int)$matches[3];

                        return ($minutes * 60 * 1000) + ($seconds * 1000) + ($hundredths * 10);
                    })
                    ->formatStateUsing(function ($state) {
                        if (!is_numeric($state)) return $state;

                        $totalSeconds = floor($state / 1000);
                        $milliseconds = floor(($state % 1000) / 10);
                        $minutes = floor($totalSeconds / 60);
                        $seconds = str_pad($totalSeconds % 60, 2, '0', STR_PAD_LEFT);
                        $formattedMs = str_pad($milliseconds, 2, '0', STR_PAD_LEFT);

                        return "{$minutes}:{$seconds}.{$formattedMs}";
                    })
                    ->rule(function () {
                        return function (string $attribute, $value, \Closure $fail) {
                            if (!preg_match('/^\d+:\d{2}\.\d{2}$/', $value)) {
                                $fail('Invalid format. Use min:sec.ms like 1:23.45');
                            }
                        };
                    })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Penalty')
            ->columns([
                Tables\Columns\TextColumn::make('crew')
                    ->label('Driver & Co-Driver')
                    ->formatStateUsing(function ($record) {

                        $driver = $record->crew->driver;
                        $coDriver = $record->crew->coDriver;

                        return "{$driver->name} {$driver->surname} / {$coDriver->name} {$coDriver->surname}";
                    })
                    ->description(fn (Model $record): string => Str::limit('Car: '.$record->crew->car.', No: '.$record->crew->crew_number, 70)),

                Tables\Columns\TextColumn::make('penalty_type')
                    ->label('Penalty Reason'),

                Tables\Columns\TextColumn::make('penalty_amount')
                    ->label('Penalty Amount')
                    ->tooltip('Time format: min:sec.ms')
                    ->formatStateUsing(function ($record) {
                        $ms = $record->penalty_amount;

                        $totalSeconds = floor($ms / 1000);
                        $milliseconds = floor(($ms % 1000) / 10);

                        $minutes = floor($totalSeconds / 60);
                        $seconds = str_pad($totalSeconds % 60, 2, '0', STR_PAD_LEFT);
                        $formattedMs = str_pad($milliseconds, 2, '0', STR_PAD_LEFT);

                        return "{$minutes}:{$seconds}.{$formattedMs}";
                    })
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Penalty'),
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
