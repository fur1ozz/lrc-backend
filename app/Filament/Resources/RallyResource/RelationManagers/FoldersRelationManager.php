<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\Folder;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FoldersRelationManager extends RelationManager
{
    protected static string $relationship = 'folders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->integer()
                    ->placeholder('Enter the folder number')
                    ->helperText('The number must be unique within the selected rally.')
                    ->rule(function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            $rallyId = $this->getOwnerRecord()->id;
                            $folderId = $get('id');

                            $exists = Folder::where('rally_id', $rallyId)
                                ->where('number', $value)
                                ->when($folderId, fn($query) => $query->where('id', '!=', $folderId))
                                ->exists();

                            if ($exists) {
                                $fail("The folder number {$value} is already used in this rally.");
                            }
                        };
                    }),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->placeholder('Enter the folder title')
                    ->helperText('Provide a clear title for the folder. Recommended format - Latvian / English '),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Folder')
            ->columns([
                TextColumn::make('number')->label('Number')->alignRight(),
                TextColumn::make('title')->label('Title')
                    ->grow()
                    ->weight(FontWeight::SemiBold),
            ])
            ->defaultSort('number')
            ->filters([
                //
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.folders.edit', $record->id))
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add folder'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
