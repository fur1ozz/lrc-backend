<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryImageResource\Pages;
use App\Filament\Resources\GalleryImageResource\RelationManagers;
use App\Models\GalleryImage;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                FileUpload::make('img_src')
                    ->label('Image')
                    ->image()
                    ->directory('gallery')
                    ->required()
                    ->openable()
                    ->downloadable()
                    ->uploadingMessage('Uploading image...')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
                Forms\Components\Select::make('rally_id')
                    ->relationship('rally', 'rally_name')
                    ->label('Rally')
                    ->required(),
                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->label('Position')
                    ->default(0),
                Forms\Components\TextInput::make('created_by')
                    ->label('Uploaded By')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('position')
            ->columns([
                Tables\Columns\ImageColumn::make('img_src')
                    ->label('Image')
                    ->square(),
                Tables\Columns\TextColumn::make('rally.rally_name')
                    ->label('Rally'),
                Tables\Columns\TextColumn::make('created_by')
                    ->label('Uploaded By')
                    ->toggleable(),
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
