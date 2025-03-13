<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FolderResource\Pages;
use App\Filament\Resources\FolderResource\RelationManagers;
use App\Models\Folder;
use App\Models\Season;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FolderResource extends Resource
{
    protected static ?string $model = Folder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rally.rally_name')->label('Rally')->searchable(),
                TextColumn::make('rally.season.year')->label('Season')->alignCenter(),
                TextColumn::make('number')->label('Number')->alignRight(),
                TextColumn::make('title')->label('Title')->grow()->weight(FontWeight::SemiBold),
            ])
            ->filters([
                SelectFilter::make('season')
                    ->label('Season')
                    ->relationship('rally.season', 'year')
                    ->default(function () {
                        return Season::where('year', Carbon::now()->year)->first()?->id ?? '';
                    }),
            ])
            ->groups(['rally.rally_name'])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->groupRecordsTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Group folders'),
            )
            ->groupingSettingsInDropdownOnDesktop()
            ->defaultSort('number')
            ->defaultGroup('rally.rally_name')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFolders::route('/'),
            'create' => Pages\CreateFolder::route('/create'),
            'edit' => Pages\EditFolder::route('/{record}/edit'),
        ];
    }
}
