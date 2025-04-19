<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FolderResource\Pages;
use App\Filament\Resources\FolderResource\RelationManagers;
use App\Models\Folder;
use App\Models\Rally;
use App\Models\Season;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FolderResource extends Resource
{
    protected static ?string $model = Folder::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Rally Info';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('number')
                            ->required()
                            ->integer()
                            ->placeholder('Enter the folder number')
                            ->helperText('The number must be unique within the selected rally.')
                            ->rules(function (Get $get) {
                                return [
                                    function ($attribute, $value, Closure $fail) use ($get) {
                                        $rally = Rally::find($get('rally_id'));
                                        $folderId = $get('id');

                                        $exists = Folder::where('rally_id', $rally->id)
                                            ->where('number', $value)
                                            ->when($folderId, fn($query) => $query->where('id', '!=', $folderId))
                                            ->exists();

                                        if ($exists) {
                                            $fail("The folder number {$value} is already used in this rally.");
                                        }
                                    },
                                ];
                            }),

                        TextInput::make('title')
                            ->required()
                            ->placeholder('Enter the folder title')
                            ->helperText('Provide a clear title for the folder. Recommended format - Latvian / English ')
                            ->maxLength(255),

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
                    ->label('Group Folders'),
            )
            ->groupingSettingsInDropdownOnDesktop()
            ->defaultSort('number')
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
            RelationManagers\DocumentsRelationManager::class
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
