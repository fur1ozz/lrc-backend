<?php

namespace App\Filament\Resources\FolderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->placeholder('e.g., Rally Regulations')
                    ->helperText('Documents are sorted alphabetically')
                    ->required(),

                Forms\Components\TextInput::make('link')
                    ->placeholder('e.g., https://example.com/document-1.pdf')
                    ->helperText('Provide a valid URL linking to the document.')
                    ->url()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Document')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                Tables\Columns\TextColumn::make('link')
                    ->label('Document Link')
                    ->weight(FontWeight::Thin)
                    ->url(fn ($record) => $record->link, true),

            ])
            ->striped()
            ->defaultSort('name')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Document'),
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
