<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\Rally;
use App\Models\Season;
use App\Models\Stage;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('stage_number')
                    ->label('Stage Number')
                    ->placeholder('1')
                    ->helperText('The stage number has to be unique for the rally')
                    ->integer()
                    ->columnSpan(3)
                    ->required()
                    ->rules(function (Get $get) {
                        return [
                            function ($attribute, $value, Closure $fail) use ($get) {
                                $rally = Rally::find($get('rally_id'));
                                $stageId = $get('id');

                                $exists = Stage::where('rally_id', $rally->id)
                                    ->where('stage_number', $value)
                                    ->when($stageId, fn($query) => $query->where('id', '!=', $stageId))
                                    ->exists();

                                if ($exists) {
                                    $fail("The stage number {$value} is already used in this rally.");
                                }
                            },
                        ];
                    }),

                TextInput::make('stage_name')
                    ->label('Stage Name')
                    ->placeholder('Stage 1 of Rally Latvia')
                    ->columnSpan(3)
                    ->required(),

                TextInput::make('distance_km')
                    ->numeric()
                    ->label('Distance (KM)')
                    ->placeholder('13,7')
                    ->helperText('Please write the distance in km')
                    ->columnSpan(['lg' => 2, 'default' => 3])
                    ->required(),

                DatePicker::make('start_date')
                    ->native(false)
                    ->label('Start Date')
                    ->placeholder('Jul 12, 2025')
                    ->columnSpan(['lg' => 2, 'default' => 3])
                    ->required()
                    ->closeOnDateSelection()
                    ->rules(function (Get $get) {
                        return [
                            function ($attribute, $value, Closure $fail) use ($get) {
                                $seasonYear = $get('id')
                                    ? Rally::find($get('rally_id'))?->season?->year
                                    : Season::find($get('season_id'))?->year;

                                if (!$seasonYear) {
                                    return;
                                }

                                if (Carbon::parse($value)->year !== (int) $seasonYear) {
                                    $fail("The date must be within the selected season's year ({$seasonYear}).");
                                }
                            },
                        ];
                    }),

                TimePicker::make('start_time')
                    ->seconds(false)
                    ->label('Start Time')
                    ->placeholder('12:00:00')
                    ->helperText('First rally car start time')
                    ->columnSpan(['lg' => 2, 'default' => 3])
                    ->required(),

            ])->columns(6);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Stage')
            ->columns([
                TextColumn::make('stage_number')->numeric()->label('Stage #')->prefix('#')->alignRight(),
                TextColumn::make('stage_name')->weight(FontWeight::Bold),
                TextColumn::make('distance_km')->suffix(' km')->label('Distance')->alignCenter(),
                TextColumn::make('start_date')->date()->sinceTooltip()->alignCenter(),
                TextColumn::make('start_time')->time()->alignCenter(),
            ])
            ->defaultSort('stage_number')
            ->filters([
                //
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.stages.edit', $record->id))
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Stage'),
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
