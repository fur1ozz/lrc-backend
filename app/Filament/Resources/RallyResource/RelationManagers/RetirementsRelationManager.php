<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\Crew;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class RetirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'retirements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('crew_id')
                    ->label('Crew')
                    ->options(function () {
                        $rally = $this->getOwnerRecord();
                        $rallyId = $rally->id;

                        $retiredCrewIds = $rally->retirements()->pluck('crew_id')->toArray();

                        return Crew::where('rally_id', $rallyId)
                            ->whereNotIn('id', $retiredCrewIds)
                            ->with(['driver', 'coDriver'])
                            ->orderByRaw('is_historic ASC, crew_number_int ASC')
                            ->get()
                            ->mapWithKeys(fn ($crew) => [
                                $crew->id => "{$crew->driver?->name} {$crew->driver?->surname} / {$crew->coDriver?->name} {$crew->coDriver?->surname} (Car: {$crew->car}, No: {$crew->crew_number})"
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->placeholder('Select a crew')
                    ->visible(fn ($get) => empty($get('id')))
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('retirement_reason')
                    ->label('Retirement Reason')
                    ->placeholder('e.g. Mechanical failure, crash, time penalty, etc.')
                    ->helperText('Briefly describe why the crew retired. Be clear and concise.')
                    ->maxLength(255)
                    ->required(),

                Forms\Components\Select::make('stage_of_retirement')
                    ->label('Stage of Retirement')
                    ->options(function () {
                        $rally = $this->getOwnerRecord();

                        return Stage::where('rally_id', $rally->id)
                            ->orderBy('stage_number')
                            ->get()
                            ->mapWithKeys(fn ($stage) => [
                                $stage->stage_number => "SS{$stage->stage_number} – {$stage->stage_name}",
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Select the stage where the crew retired. If the crew completed the stage but retired afterward, choose the next stage.')
                    ->placeholder('Select a stage')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Retirement')
            ->columns([
                Tables\Columns\TextColumn::make('crew')
                    ->label('Driver & Co-Driver')
                    ->formatStateUsing(function ($record) {

                        $driver = $record->crew->driver;
                        $coDriver = $record->crew->coDriver;

                        return "{$driver->name} {$driver->surname} / {$coDriver->name} {$coDriver->surname}";
                    })
                    ->description(fn (Model $record): string => Str::limit('Car: '.$record->crew->car.', No: '.$record->crew->crew_number, 70)),

                Tables\Columns\TextColumn::make('retirement_reason')
                    ->label('Retirement Reason')
                    ->weight(FontWeight::SemiBold)
                    ->badge()
                    ->color(Color::Red),

                Tables\Columns\TextColumn::make('stage_of_retirement')
                    ->label('Stage of Retirement')
                    ->formatStateUsing(fn ($state) => $state ? 'SS' . $state : '–')
                    ->tooltip(function ($record) {
                        $rally = $this->getOwnerRecord();

                        if ($record->stage_of_retirement) {
                            $stage = Stage::where('rally_id', $rally->id)
                                ->where('stage_number', $record->stage_of_retirement)
                                ->first();

                            return $stage?->stage_name ? $stage->stage_name : 'Unknown';
                        }

                        return 'Unknown';
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Retirement'),
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
