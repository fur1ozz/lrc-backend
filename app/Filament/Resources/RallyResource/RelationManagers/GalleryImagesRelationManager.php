<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GalleryImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'galleryImages';

    protected static ?string $title = 'Gallery';
    public function form(Form $form): Form
    {
        return $form
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
                    ->columnSpanFull(),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('created_by')
                            ->label('Created By')
                            ->helperText('You can add the creator of these images.')
                            ->maxLength(255),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Image')
            ->columns([
                Tables\Columns\ImageColumn::make('img_src')
                    ->label('Image')
                    ->square()
                    ->grow(),

                Tables\Columns\TextColumn::make('created_by')
                    ->label('Created By')
                    ->default('Unknown')
                    ->searchable()
                    ->color(fn ($state) => $state === 'Unknown' ? 'gray' : 'black')
                    ->weight(fn ($state) => $state === 'Unknown' ? FontWeight::Thin : FontWeight::Bold)
                    ->extraAttributes(fn ($state) => $state === 'Unknown' ? ['class' => 'italic'] : []),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->alignCenter()
                    ->dateTime('M d, Y H:i')
                    ->sinceTooltip(),
            ])
            ->filters([
                //
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
