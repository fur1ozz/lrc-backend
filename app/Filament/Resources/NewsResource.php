<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Filament\Resources\NewsResource\RelationManagers;
use App\Models\News;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'Rally Info';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required(),

                                Forms\Components\Textarea::make('paragraph')
                                    ->autosize()
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                FileUpload::make('img_src')
                                    ->label('Image')
                                    ->helperText('This image will be shown as the main banner image for the news article.')
                                    ->image()
                                    ->directory('news')
                                    ->required()
                                    ->openable()
                                    ->downloadable()
                                    ->uploadingMessage('Uploading image...')
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ])
                    ]),

                Section::make()
                    ->schema([
                        Forms\Components\RichEditor::make('body')
                            ->fileAttachmentsDirectory('news_article'),
                    ]),

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
                                Forms\Components\DateTimePicker::make('pub_date_time')
                                    ->native(false)
                                    ->label('Publication Date and Time')
                                    ->helperText('Set the date and time when this article will be made live and accessible to users.')
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('img_src')
                    ->label('Image'),

                TextColumn::make('title')
                    ->label('News Title and Description')
                    ->searchable()
                    ->description(fn (Model $record): string => Str::limit($record->paragraph, 70)),

                TextColumn::make('pub_date_time')
                    ->label('Publish Date')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('M d, Y H:i'))
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
            ->defaultSort('pub_date_time', 'desc')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
