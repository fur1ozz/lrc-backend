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

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('penalty_minutes')
                            ->label('Minutes')
                            ->placeholder('Enter minutes')
                            ->mask(99)
                            ->numeric()
                            ->helperText('Up to 99 minutes')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                $totalMs = (int) $get('penalty_amount');
                                $minutes = floor($totalMs / 1000 / 60);
                                $set('penalty_minutes', $minutes > 0 ? $minutes : null);
                            })
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $minutes = (int) $state;
                                $seconds = (int) $get('penalty_seconds');
                                $milliseconds = (int) $get('penalty_milliseconds');
                                $set('penalty_amount', ($minutes * 60 * 1000) + ($seconds * 1000) + $milliseconds);
                            }),

                        Forms\Components\TextInput::make('penalty_seconds')
                            ->label('Seconds')
                            ->placeholder('Enter seconds')
                            ->mask(99)
                            ->numeric()
                            ->maxValue(59)
                            ->helperText('Up to 59 seconds')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                $totalMs = (int) $get('penalty_amount');
                                $seconds = floor(($totalMs / 1000) % 60);
                                $set('penalty_seconds', $seconds > 0 ? $seconds : null);
                            })
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $minutes = (int) $get('penalty_minutes');
                                $seconds = (int) $state;
                                $milliseconds = (int) $get('penalty_milliseconds');
                                $set('penalty_amount', ($minutes * 60 * 1000) + ($seconds * 1000) + $milliseconds);
                            }),

                        Forms\Components\TextInput::make('penalty_milliseconds')
                            ->label('Milliseconds')
                            ->placeholder('Enter milliseconds')
                            ->mask(999)
                            ->numeric()
                            ->helperText('Up to 999 milliseconds')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                $totalMs = (int) $get('penalty_amount');
                                $milliseconds = $totalMs % 1000;
                                $set('penalty_milliseconds', $milliseconds > 0 ? $milliseconds : null);
                            })
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $minutes = (int) $get('penalty_minutes');
                                $seconds = (int) $get('penalty_seconds');
                                $milliseconds = (int) $state;
                                $set('penalty_amount', ($minutes * 60 * 1000) + ($seconds * 1000) + $milliseconds);
                            }),
                    ])->columns(3),

                Forms\Components\Hidden::make('penalty_amount')
                    ->required()
                    ->rule(['numeric', 'min:10']),

                Forms\Components\Placeholder::make('penalty_error_display')
                    ->label('')
                    ->content(fn ($get) => (int) $get('penalty_amount') < 10 ? 'The penalty must be at least 10 milliseconds.' : null)
                    ->visible(fn ($get) => (int) $get('penalty_amount') < 10)
                    ->extraAttributes(function ($get) {
                        $isEditing = !empty($get('id'));
                        $errorColor = $isEditing ? 'color: red; font-weight: bold;' : 'color: yellow; font-weight: bold;';

                        return ['style' => $errorColor];
                    }),
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
                    ->formatStateUsing(function ($record) {
                        $ms = $record->penalty_amount;

                        $totalSeconds = floor($ms / 1000);
                        $milliseconds = floor(($ms % 1000) / 10);
                        $minutes = floor($totalSeconds / 60);
                        $seconds = $totalSeconds % 60;

                        $timeComponents = [];

                        if ($minutes > 0) {
                            $timeComponents[] = "{$minutes}min";
                        }

                        if ($seconds > 0) {
                            $timeComponents[] = "{$seconds}sec";
                        }

                        if ($milliseconds > 0) {
                            $timeComponents[] = "{$milliseconds}ms";
                        }

                        return implode(' ', $timeComponents);
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
