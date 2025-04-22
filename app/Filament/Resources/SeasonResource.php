<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeasonResource\Pages;
use App\Filament\Resources\SeasonResource\RelationManagers;
use App\Models\Season;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Overall Data';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('year')
                                    ->required()
                                    ->integer()
                                    ->minValue(2024)
                                    ->maxValue(2035)
                                    ->placeholder('2025')
                                    ->columnSpan(1),
                            ])->columns(2),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Placeholder::make('championship_classes_display')
                                    ->label('Grouped Local Championship Classes')
                                    ->helperText('These are the classes assigned to this season for tracking and saving championship points.')
                                    ->content(function ($record) {
                                        return new HtmlString(
                                            $record->championshipClasses
                                                ->load('group')
                                                ->groupBy(fn ($class) => $class->group?->group_name ?? 'Ungrouped')
                                                ->map(function ($classes, $groupName) {
                                                    $classNames = $classes->pluck('class_name')->join(', ');
                                                    return "<div class='my-2'>
                                                <strong class='text-primary-500'>{$groupName}:</strong>
                                                <span class='text-gray-400'>{$classNames}</span>
                                            </div>";
                                                })
                                                ->join('')
                                        );
                                    }),

                                Forms\Components\Select::make('championshipClasses')
                                    ->label('Local Championship Classes')
                                    ->multiple()
                                    ->relationship('championshipClasses', 'class_name')
                                    ->options(function ($record) {
                                        if (! $record) return [];

                                        $classes = \App\Models\GroupClass::with('group')->get();

                                        return $classes->groupBy(fn ($cc) => $cc->group->group_name ?? 'Other')
                                            ->mapWithKeys(function ($grouped, $groupName) {
                                                return [
                                                    $groupName => $grouped->mapWithKeys(function ($class) {
                                                        return [$class->id => $class->class_name];
                                                    }),
                                                ];
                                            });
                                    })
                                    ->helperText('Select this seasons classes, that will get included in local championship')
                                    ->preload()
                                    ->searchable(),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('year')->sortable()->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('championshipClasses.class_name')
                    ->label('Local Championship Classes')
                    ->badge()
                    ->limitList(3)
                    ->colors(['primary'])
                    ->listWithLineBreaks()
                    ->expandableLimitedList()
                    ->alignCenter()
            ])
            ->striped()
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color(Color::Sky),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeasons::route('/'),
            'create' => Pages\CreateSeason::route('/create'),
            'edit' => Pages\EditSeason::route('/{record}/edit'),
        ];
    }
}
