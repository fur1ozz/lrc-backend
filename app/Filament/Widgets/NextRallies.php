<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RallyResource;
use App\Models\Rally;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NextRallies extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming Rallies';

    public function table(Table $table): Table
    {
        return $table
            ->query(Rally::where('date_from', '>', now()->toDateString()))
            ->defaultSort('date_from', 'asc')
            ->columns([
                TextColumn::make('rally_name')->searchable()->weight(FontWeight::Bold),
                TextColumn::make('date_from')->date()->sinceTooltip(),
                TextColumn::make('date_to')->date(),
                TextColumn::make('location')->badge()->color('purple')->alignCenter(),
                TextColumn::make('road_surface')->alignCenter()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gravel' => 'gravel',
                        'tarmac' => 'tarmac',
                        'snow' => 'snow',
                    }),
                TextColumn::make('season.year'),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.rallies.edit', $record->id))
            ->defaultPaginationPageOption(5);
    }
}
