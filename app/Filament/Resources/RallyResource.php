<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RallyResource\Pages;
use App\Filament\Resources\RallyResource\RelationManagers;
use App\Models\Rally;
use App\Models\Season;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RallyResource extends Resource
{
    protected static ?string $model = Rally::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('rally_name')->columnSpan(2),
                DatePicker::make('date_from')
                    ->native(false)
                    ->displayFormat('Y/m/d')
                    ->closeOnDateSelection()
                    ->prefix('Rally Starts'),
                DatePicker::make('date_to')
                    ->native(false)
                    ->displayFormat('Y/m/d')
                    ->afterOrEqual('date_from')
                    ->closeOnDateSelection()
                    ->prefix('Rally Ends'),
                Select::make('location')
                    ->options([
                        'lv' => 'Latvia',
                        'ee' => 'Estonia',
                        'lt' => 'Lithuania',
                    ])
                    ->native(false)
                    ->required(),
                ToggleButtons::make('road_surface')
                    ->options([
                        'gravel' => 'Gravel',
                        'tarmac' => 'Tarmac',
                        'snow' => 'Snow',
                    ])
                    ->colors([
                        'gravel' => 'gravel',
                        'tarmac' => 'tarmac',
                        'snow' => 'snow',
                    ])->inline(),
                Select::make('season_id')
                    ->label('Season')
                    ->options(
                        Season::all()->pluck('year', 'id')->toArray()
                    )
                    ->searchable()
                    ->required(),
                TextInput::make('rally_tag')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('rally_name')->searchable(),
                TextColumn::make('date_from')->sortable(),
                TextColumn::make('date_to'),
                TextColumn::make('location')->badge()->color('purple'),
                TextColumn::make('road_surface')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gravel' => 'gravel',
                        'tarmac' => 'tarmac',
                        'snow' => 'snow',
                    }),
                TextColumn::make('season.year')->sortable(),
            ])
            ->filters([
                SelectFilter::make('season_id')
                    ->label('Season')
                    ->options([
                        Season::all()->pluck('year', 'id')->toArray()
                    ]),
            ])
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
            'index' => Pages\ListRallies::route('/'),
            'create' => Pages\CreateRally::route('/create'),
            'edit' => Pages\EditRally::route('/{record}/edit'),
        ];
    }
}
