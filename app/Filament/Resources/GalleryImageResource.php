<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryImageResource\Pages;
use App\Filament\Resources\GalleryImageResource\RelationManagers;
use App\Models\GalleryImage;
use App\Models\Rally;
use App\Models\Season;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Rally Info';

    protected static ?string $navigationLabel = 'Gallery';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        FileUpload::make('img_src')
                            ->label('Images')
                            ->image()
                            ->directory('gallery')
                            ->required()
                            ->openable()
                            ->downloadable()
                            ->uploadingMessage('Uploading image...')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->multiple(fn ($get) => empty($get('id')))
                            ->helperText(fn ($get) => empty($get('id')) ? 'You can upload multiple images here.' : ''),
                    ])->collapsible(),

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

                Forms\Components\Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('created_by')
                                    ->label('Created By')
                                    ->helperText('You can add the creator of these images.'),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('img_src')
                    ->label('Image')
                    ->square()
                    ->grow(),

                Tables\Columns\TextColumn::make('created_by')
                    ->label('Created By')
                    ->default('Unknown')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->alignCenter()
                    ->dateTime('M d, Y H:i')
                    ->sinceTooltip(),
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
                    ->label('Group Images'),
            )
            ->groupingSettingsInDropdownOnDesktop()
            ->defaultSort('created_at', 'desc')
            ->defaultGroup(
                Group::make('rally.date_from')
                    ->getTitleFromRecordUsing(fn (Model $record): string => ucfirst($record->rally->rally_name))
                    ->collapsible()
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
            'index' => Pages\ListGalleryImages::route('/'),
            'create' => Pages\CreateGalleryImage::route('/create'),
            'edit' => Pages\EditGalleryImage::route('/{record}/edit'),
        ];
    }
}
