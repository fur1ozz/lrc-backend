<?php

namespace App\Filament\Resources\StageResource\RelationManagers;

use App\Models\Crew;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class StartTimesRelationManager extends RelationManager
{
    protected static string $relationship = 'startTimes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('crew_id')
                    ->label('Crew')
                    ->options(function (callable $get) {
                        $stage = $this->getOwnerRecord();
                        $rallyId = $stage->rally->id;

                        // Filter crews by the rally ID
                        return Crew::where('rally_id', $rallyId)
                            ->with(['driver', 'coDriver'])
                            ->orderByRaw('is_historic ASC, crew_number_int ASC')
                            ->get()
                            ->mapWithKeys(function ($crew) {
                                return [
                                    $crew->id => "{$crew->driver?->name} {$crew->driver?->surname} / {$crew->coDriver?->name} {$crew->coDriver?->surname} (Car: {$crew->car}, No: {$crew->crew_number})"
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->placeholder('Select a crew'),

                Forms\Components\TimePicker::make('start_time')
                    ->required()
                    ->seconds(false)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('crew_id')
            ->columns([
                Tables\Columns\TextColumn::make('crew')
                    ->label('Driver & Co-Driver')
                    ->formatStateUsing(function ($record) {

                        $driver = $record->crew->driver;
                        $coDriver = $record->crew->coDriver;

                        return "{$driver->name} {$driver->surname} / {$coDriver->name} {$coDriver->surname}";
                    })
                    ->description(fn (Model $record): string => Str::limit('Car: '.$record->crew->car.', No: '.$record->crew->crew_number, 70)),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->sortable()
                    ->grow()
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i')),
            ])
            ->defaultSort('start_time')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
