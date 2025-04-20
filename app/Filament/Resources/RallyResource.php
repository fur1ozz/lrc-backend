<?php

namespace App\Filament\Resources;

use App\Enums\RoadSurfaceEnum;
use App\Filament\Resources\RallyResource\Pages;
use App\Filament\Resources\RallyResource\RelationManagers;
use App\Models\Rally;
use App\Models\Season;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RallyResource extends Resource
{
    protected static ?string $model = Rally::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Rally Info';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'rally_name';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Season' => $record->season->year,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rally Info')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(['default' => 1, 'lg' => 2])
                            ->schema([
                                TextInput::make('rally_name')
                                    ->label('Rally Name')
                                    ->columnSpan(fn ($get) => empty($get('id')) ? 1 : 2)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (empty($get('id'))) {
                                            $state = str_ireplace('Rallysprint', 'rally-sprint', $state);
                                            $state = ucwords(strtolower($state));

                                            $set('rally_tag', Str::slug($state));
                                        }
                                    })
                                    ->helperText('Only "Rallysprint" or "Rally" are allowed.')
                                    ->placeholder('Rally Latvia / Rallysprint Latvia')
                                    ->maxLength(255)
                                    ->required(),

                                TextInput::make('rally_tag')
                                    ->label('Rally Slug')
                                    ->disabled()
                                    ->visible(fn ($get) => empty($get('id')))
                                    ->helperText('This slug will be generated automatically based on the rally name and cannot be changed once created.'),
                            ]),

                        DatePicker::make('date_from')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->closeOnDateSelection()
                            ->prefix('Rally Starts')
                            ->rules(function (Get $get) {
                                return [
                                    function ($attribute, $value, Closure $fail) use ($get) {
                                        $season = Season::find($get('season_id'));

                                        $seasonYear = (int) $season?->year;
                                        $selectedYear = Carbon::parse($value)->year;

                                        if ($selectedYear !== $seasonYear) {
                                            $fail("The date must be within the selected season's year ({$seasonYear}).");
                                        }
                                    },
                                ];
                            })
                            ->required(),

                        DatePicker::make('date_to')
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->afterOrEqual('date_from')
                            ->closeOnDateSelection()
                            ->prefix('Rally Ends')
                            ->required(),

                        Select::make('location')
                            ->options([
                                'lv' => 'Latvia',
                                'ee' => 'Estonia',
                                'lt' => 'Lithuania',
                                'pl' => 'Poland',
                                'fi' => 'Finland',
                            ])
                            ->native(false)
                            ->placeholder('Select the location')
                            ->required(),

                        ToggleButtons::make('road_surface')
                            ->options([
                                RoadSurfaceEnum::GRAVEL->value => RoadSurfaceEnum::GRAVEL->label(),
                                RoadSurfaceEnum::TARMAC->value => RoadSurfaceEnum::TARMAC->label(),
                                RoadSurfaceEnum::SNOW->value => RoadSurfaceEnum::SNOW->label(),
                            ])
                            ->colors([
                                RoadSurfaceEnum::GRAVEL->value => RoadSurfaceEnum::GRAVEL->value,
                                RoadSurfaceEnum::TARMAC->value => RoadSurfaceEnum::TARMAC->value,
                                RoadSurfaceEnum::SNOW->value => RoadSurfaceEnum::SNOW->value,
                            ])
                            ->inline()
                            ->required(),

                        Select::make('season_id')
                            ->label('Season')
                            ->options(
                                Season::all()->pluck('year', 'id')->toArray()
                            )
                            ->searchable()
                            ->placeholder('Select the Season')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Rally Media')
                    ->description('Provide a rally preview image and banner for the calendar and event pages.')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        FileUpload::make('rally_img')
                            ->label('Rally Image')
                            ->helperText('This image will appear on the calendar as a quick preview of the rally.')                            ->image()
                            ->directory('rally_images')
                            ->required()
                            ->openable()
                            ->downloadable()
                            ->uploadingMessage('Uploading image...')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),

                        FileUpload::make('rally_banner')
                            ->label('Rally Banner')
                            ->image()
                            ->directory('rally_banners')
                            ->openable()
                            ->downloadable()
                            ->uploadingMessage('Uploading image...')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('983:235')
                            ->imageResizeTargetWidth(983)
                            ->imageResizeTargetHeight(235)
                    ])
                    ->collapsible()
                    ->collapsed(fn (Get $get) => filled($get('id')))
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('rally_name')->searchable()->weight(FontWeight::Bold),
                TextColumn::make('date_from')->sortable()->date()->sinceTooltip(),
                TextColumn::make('date_to')->date(),
                TextColumn::make('location')->badge()->color('purple')->alignCenter(),
                TextColumn::make('road_surface')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (RoadSurfaceEnum $state): string => match ($state) {
                        RoadSurfaceEnum::GRAVEL => RoadSurfaceEnum::GRAVEL->value,
                        RoadSurfaceEnum::TARMAC => RoadSurfaceEnum::TARMAC->value,
                        RoadSurfaceEnum::SNOW => RoadSurfaceEnum::SNOW->value,
                    }),
                TextColumn::make('season.year')->sortable(),
                TextColumn::make('rally_tag')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->striped()
            ->defaultSort('date_from', 'desc')
            ->searchPlaceholder('Search (Rally Name)')
            ->recordClasses(function ($record) {
                return $record->date_to < now()->toDateString()
                    ? 'opacity-50'
                    : '';
            })
            ->filters([
                SelectFilter::make('season_id')
                    ->label('Season')
                    ->relationship('season', 'year')
                    ->default(function () {
                        return Season::where('year', Carbon::now()->year)->first()?->id ?? '';
                    }),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->toggleColumnsTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Toggle Columns'),
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
            RelationGroup::make('News, Stages and Documents', [
                RelationManagers\NewsRelationManager::class,
                RelationManagers\StagesRelationManager::class,
                RelationManagers\FoldersRelationManager::class,
            ]),
            RelationGroup::make('Sponsors and Classes',[
                RelationManagers\RallySponsorsRelationManager::class,
                RelationManagers\RallyClassesRelationManager::class,
            ]),
            RelationGroup::make('Crews', [
                //
            ]),
            RelationGroup::make('Results', [
                // probably only overall results with auto calculation button (calculate/recalculate)
                // maybe retirements here
            ]),
            RelationGroup::make('Winner and Gallery', [
                RelationManagers\RallyWinnerRelationManager::class,
                RelationManagers\GalleryImagesRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRallies::route('/'),
            'create' => Pages\CreateRally::route('/create'),
            'edit' => Pages\EditRally::route('/{record}/edit'),
        ];
    }
}
