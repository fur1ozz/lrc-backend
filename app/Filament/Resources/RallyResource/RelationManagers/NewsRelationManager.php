<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\Rally;
use App\Models\Season;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class NewsRelationManager extends RelationManager
{
    protected static string $relationship = 'news';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),

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
                    ->schema([]),

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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('News Article')
            ->columns([
                ImageColumn::make('img_src')
                    ->label('Image'),

                TextColumn::make('title')
                    ->label('News Title and Description')
                    ->description(fn (Model $record): string => Str::limit($record->paragraph, 70)),

                TextColumn::make('pub_date_time')
                    ->label('Publish Date')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('M d, Y H:i'))
            ])
            ->defaultSort('pub_date_time', 'desc')
            ->filters([
                //
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.news.edit', $record->id))
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add News Article'),
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
