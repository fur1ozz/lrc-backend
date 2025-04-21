<?php

namespace App\Filament\Resources\StageResource\RelationManagers;

use App\Models\Crew;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StageResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'stageResults';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('crew_id')
                    ->label('Crew')
                    ->options(function () {
                        $stage = $this->getOwnerRecord();
                        $rallyId = $stage->rally_id;

                        $retiredCrewIds = $stage->stageResults()->pluck('crew_id')->toArray();

                        return Crew::where('rally_id', $rallyId)
                            ->whereNotIn('id', $retiredCrewIds)
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
                    ->visible(fn ($get) => empty($get('id')))
                    ->columnSpanFull(),

                Forms\Components\Section::make('Time Taken')
                    ->schema([
                        Forms\Components\TextInput::make('time_minutes')
                            ->label('Minutes')
                            ->placeholder('Enter minutes')
                            ->mask(99)
                            ->numeric()
                            ->helperText('0–99 min')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                $totalMs = (int) $get('time_taken');
                                $minutes = floor($totalMs / 1000 / 60);
                                $set('time_minutes', $minutes > 0 ? $minutes : null);
                            })
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $minutes = (int) $state;
                                $seconds = (int) $get('time_seconds');
                                $ms2 = (int) $get('time_milliseconds');
                                $set('time_taken', ($minutes * 60 * 1000) + ($seconds * 1000) + ($ms2 * 10));

                                $this->updateAvgSpeed($get, $set);

                            }),

                        Forms\Components\TextInput::make('time_seconds')
                            ->label('Seconds')
                            ->placeholder('Enter seconds')
                            ->mask(99)
                            ->numeric()
                            ->maxValue(59)
                            ->helperText('0–59 sec')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                $totalMs = (int) $get('time_taken');
                                $seconds = floor(($totalMs / 1000) % 60);
                                $set('time_seconds', $seconds > 0 ? $seconds : null);
                            })
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $minutes = (int) $get('time_minutes');
                                $seconds = (int) $state;
                                $ms2 = (int) $get('time_milliseconds');
                                $set('time_taken', ($minutes * 60 * 1000) + ($seconds * 1000) + ($ms2 * 10));

                                $this->updateAvgSpeed($get, $set);

                            }),

                        Forms\Components\TextInput::make('time_milliseconds')
                            ->label('Milliseconds')
                            ->placeholder('e.g. 37')
                            ->mask(99)
                            ->numeric()
                            ->helperText('Two digits only (0–99)')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                $totalMs = (int) $get('time_taken');
                                $ms = $totalMs % 1000;
                                $ms2Digits = floor($ms / 10);
                                $set('time_milliseconds', $ms2Digits > 0 ? $ms2Digits : null);
                            })
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $minutes = (int) $get('time_minutes');
                                $seconds = (int) $get('time_seconds');
                                $ms2 = (int) $state;
                                $set('time_taken', ($minutes * 60 * 1000) + ($seconds * 1000) + ($ms2 * 10));

                                $this->updateAvgSpeed($get, $set);
                            }),

                    ])
                    ->columns(3),

                Forms\Components\TextInput::make('avg_speed')
                    ->label('Average Speed (km/h)')
                    ->readonly()
                    ->helperText('Automatically calculated')
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 2);
                    })
                    ->rule('gt:0'),

                Forms\Components\Placeholder::make('info_text')
                    ->label('')
                    ->content('Note: You won\'t be able to submit this form if you haven\'t provided any time.')
                    ->extraAttributes(['style' => 'color: #E4E4E4;'])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('time_taken')
                    ->required()
                    ->rule(['numeric']),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Stage Result')
            ->columns([
                Tables\Columns\TextColumn::make('crew.driver.name')
                    ->label('Driver & Co-Driver')
                    ->formatStateUsing(function ($record) {

                        $driver = $record->crew->driver;
                        $coDriver = $record->crew->coDriver;

                        return "{$driver->name} {$driver->surname} / {$coDriver->name} {$coDriver->surname}";
                    })
                    ->description(fn (Model $record): string => Str::limit('Car: '.$record->crew->car.', No: '.$record->crew->crew_number, 70)),

                Tables\Columns\TextColumn::make('time_taken')
                    ->label('Time Taken')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        $ms = $state;

                        $totalSeconds = $ms / 1000;
                        $hours = floor($totalSeconds / 3600);
                        $minutes = floor(($totalSeconds % 3600) / 60);
                        $seconds = floor($totalSeconds % 60);
                        $milliseconds = floor(($ms % 1000) / 10);

                        $formatted = '';

                        if ($hours > 0) {
                            $formatted .= $hours . ':';
                        }

                        $formatted .= str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':';
                        $formatted .= str_pad($seconds, 2, '0', STR_PAD_LEFT) . '.';
                        $formatted .= str_pad($milliseconds, 2, '0', STR_PAD_LEFT);

                        return $formatted;
                    }),

                Tables\Columns\TextColumn::make('avg_speed')
                    ->label('Average Speed')
                    ->suffix(' km/h'),
            ])
            ->defaultSort('time_taken')
            ->filters([
                Tables\Filters\Filter::make('driver_name')
                    ->form([
                        Forms\Components\TextInput::make('driver')
                            ->label('Search by Driver Name')
                            ->placeholder('Type driver name...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereHas('crew.driver', function ($q) use ($data) {
                            $q->where('name', 'like', '%' . $data['driver'] . '%');
                        });
                    }),
                Tables\Filters\Filter::make('crew_number')
                    ->form([
                        Forms\Components\TextInput::make('number')
                            ->label('Search by Crew Number')
                            ->placeholder('e.g. 12'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!filled($data['number'])) {
                            return $query;
                        }

                        return $query->whereHas('crew', function ($q) use ($data) {
                            $q->where('crew_number_int', $data['number']);
                        });
                    }),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Stage Result'),
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

    protected function updateAvgSpeed(callable $get, callable $set)
    {
        $time_taken = $get('time_taken');
        $stage = $this->getOwnerRecord();
        $distance_km = $stage->distance_km;

        if ($time_taken > 0 && $distance_km > 0) {
            $time_taken_in_hours = $time_taken / (1000 * 3600);
            $avg_speed = $distance_km / $time_taken_in_hours;

            $avg_speed = round($avg_speed, 2);

            $set('avg_speed', $avg_speed);
        }
    }
}
