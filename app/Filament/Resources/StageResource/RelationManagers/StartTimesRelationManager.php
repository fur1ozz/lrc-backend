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
use Filament\Forms\Components\Actions\Action;

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

                        $crewsWithStartTimes = $stage->startTimes()->pluck('crew_id')->toArray();

                        return Crew::where('rally_id', $rallyId)
                            ->whereNotIn('id', $crewsWithStartTimes)
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
                    ->helperText('Only crews without a start time will show')
                    ->visible(fn ($get) => empty($get('id'))),

                Forms\Components\TimePicker::make('start_time')
                    ->required()
                    ->seconds(false)
                    ->helperText('Start times cant be duplicated')
                    ->rules([
                        function (callable $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $stage = $this->getOwnerRecord();
                                $crewId = $get('crew_id');

                                $exists = $stage->startTimes()
                                    ->where('start_time', 'LIKE', \Carbon\Carbon::parse($value)->format('H:i') . '%')
                                    ->when($crewId, fn ($query) => $query->where('crew_id', '!=', $crewId))
                                    ->exists();

                                if ($exists) {
                                    $fail("This start time is already assigned to another crew.");
                                }
                            };
                        },
                    ])
                    ->hintAction(
                        Action::make('nextFromLast')
                            ->label('Last +1min')
                            ->icon('heroicon-s-clock')
                            ->tooltip('Click to automatically set the start time by adding 1 minute to the last recorded start time.')
                            ->action(function (callable $get, callable $set) {
                                $stage = $this->getOwnerRecord();

                                $lastStartTime = $stage->startTimes()
                                    ->orderBy('start_time', 'desc')
                                    ->first()?->start_time;

                                if ($lastStartTime) {
                                    $nextStartTime = \Carbon\Carbon::parse($lastStartTime)->addMinute()->format('H:i');
                                    $set('start_time', $nextStartTime);
                                } else {
                                    $set('start_time', '08:00');
                                }
                            })
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
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
                    ->badge()
                    ->color(Color::Sky)
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i')),
            ])
            ->defaultSort('start_time')
            ->striped()
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
