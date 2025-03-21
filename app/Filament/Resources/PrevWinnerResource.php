<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrevWinnerResource\Pages;
use App\Filament\Resources\PrevWinnerResource\RelationManagers;
use App\Models\Crew;
use App\Models\PrevWinner;
use App\Models\Rally;
use App\Models\Season;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PrevWinnerResource extends Resource
{
    protected static ?string $model = PrevWinner::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Overall Data';

    protected static ?string $navigationLabel = 'Winners';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): string
    {
        return Rally::where('date_to', '<=', Carbon::today())
        ->whereNotIn('id', PrevWinner::pluck('rally_id'))
        ->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Number of rallies with unassigned winners';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('rally_id')
                            ->label('Rally')
                            ->options(fn () => Rally::whereNotIn('id', PrevWinner::pluck('rally_id'))
                                ->where('date_to', '<=', Carbon::today())
                                ->with('season')
                                ->get()
                                ->mapWithKeys(fn ($rally) => [
                                    $rally->id => "{$rally->rally_name} ({$rally->season?->year})"
                                ])
                                ->toArray())
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('crew_id', null);
                            })
                            ->searchable()
                            ->native(false)
                            ->placeholder('Choose a rally')
                            ->helperText('Only finished rallies and rallies that have not been created a winner will show'),
                    ])
                    ->visible(fn ($get) => empty($get('id')))
                    ->columnSpanFull(),
                Forms\Components\Group::make()
                    ->schema([
                        Section::make('Winners Info')
                            ->schema([
                                Select::make('crew_id')
                                    ->label('Crew')
                                    ->options(fn (callable $get) => Crew::where('rally_id', $get('rally_id'))
                                        ->with(['driver', 'coDriver'])
                                        // This orders the crews in ascending order, firstly for default numbers, and then for historic class numbers, like H1
                                        // Also this fixes issue of sorting incorrectly sorting numbers as string, for example it sorted like this (2, 10, 1), now (10, ..., 2, 1)
                                        ->orderByRaw('
                                            CASE
                                                WHEN crew_number REGEXP "^[0-9]+$" THEN 0
                                                ELSE 1
                                            END ASC
                                        ')
                                        ->orderByRaw('CAST(REGEXP_REPLACE(crew_number, \'[^0-9]\', \'\') AS UNSIGNED) ASC')
                                        ->orderByRaw('REGEXP_REPLACE(crew_number, \'[0-9]\', \'\') ASC')
                                        ->get()
                                        ->mapWithKeys(fn ($crew) => [
                                            $crew->id => "{$crew->driver?->name} {$crew->driver?->surname} & {$crew->coDriver?->name} {$crew->coDriver?->surname} (Car: {$crew->car}, No: {$crew->crew_number})"
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Select a crew')
                                    ->helperText(fn ($get) => empty($get('id')) ? 'Crews will change based on the rally' : '')
                                    ->disabled(fn ($get) => !$get('rally_id'))
                                    ->rule(function (callable $get) {
                                        return 'exists:crews,id,rally_id,' . $get('rally_id');
                                    }),

                                Forms\Components\Textarea::make('feedback')
                                    ->required()
                                    ->helperText('Crews feedback about the rally')
                                    ->rows(3)
                                    ->autosize()
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                FileUpload::make('winning_img')
                                    ->label('Image')
                                    ->image()
                                    ->directory('rally_winners')
                                    ->required()
                                    ->openable()
                                    ->downloadable()
                                    ->uploadingMessage('Uploading image...')
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->helperText('The image of the winning crew')
                            ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rally.rally_name')
                    ->description(fn (Model $record): string => 'Season: '.$record->rally->season->year),

                Tables\Columns\TextColumn::make('crew')
                    ->label('Driver & Co-Driver')
                    ->formatStateUsing(function ($record) {

                        $driver = $record->crew->driver;
                        $coDriver = $record->crew->coDriver;

                        return "{$driver->name} {$driver->surname} & {$coDriver->name} {$coDriver->surname}";
                    })
                    ->description(fn (Model $record): string => Str::limit('Car: '.$record->crew->car.', No: '.$record->crew->crew_number, 70)),

                Tables\Columns\TextColumn::make('feedback')
                    ->wrap()
                    ->limit(100)
                    ->grow(),

                Tables\Columns\ImageColumn::make('winning_img')
                    ->label('Image')
                    ->alignCenter()
                    ->square(),
            ])
            ->filters([
                SelectFilter::make('season')
                    ->label('Season')
                    ->relationship('rally.season', 'year')
                    ->default(function () {
                        return Season::where('year', Carbon::now()->year)->first()?->id ?? '';
                    }),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrevWinners::route('/'),
            'create' => Pages\CreatePrevWinner::route('/create'),
            'edit' => Pages\EditPrevWinner::route('/{record}/edit'),
        ];
    }
}
