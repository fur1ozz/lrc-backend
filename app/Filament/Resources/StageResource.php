<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StageResource\Pages;
use App\Filament\Resources\StageResource\RelationManagers;
use App\Models\Rally;
use App\Models\Season;
use App\Models\Stage;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Rally Info';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'stage_name';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Rally' => $record->rally->rally_name,
            'Season' => $record->rally->season->year,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
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
                            ->maxLength(255)
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

                    ])->columns(6),
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Association')
                            ->schema([
                                Select::make('season_id')
                                    ->label('Season')
                                    ->options(Season::all()->pluck('year', 'id'))
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('rally_id', null);
                                    })
                                    ->visible(fn ($get) => empty($get('id')))
                                    ->required()
                                    ->native(false)
                                    ->placeholder('Choose a season'),

                                Select::make('rally_id')
                                    ->label('Rally')
                                    ->options(fn (callable $get) => Rally::where('season_id', $get('season_id'))->pluck('rally_name', 'id')->toArray())
                                    ->required()
                                    ->visible(fn ($get) => empty($get('id')))
                                    ->disabled(fn ($get) => $get('season_id') === null)
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('Choose a rally'),
                            ])
                            ->columns(2)
                            ->visible(fn ($get) => empty($get('id'))),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stage_number')->numeric()->label('Stage #')->prefix('#')->alignRight(),
                TextColumn::make('stage_name')->searchable()->weight(FontWeight::Bold),
                TextColumn::make('distance_km')->suffix(' km')->label('Distance')->alignCenter(),
                TextColumn::make('start_date')->date()->sinceTooltip()->alignCenter(),
                TextColumn::make('start_time')->time()->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('season')
                    ->label('Season')
                    ->relationship('rally.season', 'year')
                    ->default(function () {
                        return Season::where('year', Carbon::now()->year)->first()?->id ?? '';
                    }),
            ])
            ->groups([
                Group::make('rally.date_from')
                    ->getTitleFromRecordUsing(fn (Model $record): string => ucfirst($record->rally->rally_name))
                    ->collapsible()
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->groupRecordsTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Group Stages'),
            )
            ->groupingSettingsInDropdownOnDesktop()
            ->defaultSort('stage_number')
            ->defaultGroup(
                Group::make('rally.date_from')
                    ->getTitleFromRecordUsing(fn (Model $record): string => ucfirst($record->rally->rally_name))
            )
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color(Color::Sky),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Splits and Split Times', [
                RelationManagers\SplitsRelationManager::class,
            ]),
            RelationGroup::make('Start times', [
                RelationManagers\StartTimesRelationManager::class,
            ]),
            RelationGroup::make('Stage Results', [
                //todo change to corect one
                RelationManagers\SplitsRelationManager::class,
            ]),
            RelationGroup::make('Penalties', [
                RelationManagers\PenaltiesRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStages::route('/'),
            'create' => Pages\CreateStage::route('/create'),
            'edit' => Pages\EditStage::route('/{record}/edit'),
        ];
    }
}
