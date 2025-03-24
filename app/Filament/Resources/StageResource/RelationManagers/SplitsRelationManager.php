<?php

namespace App\Filament\Resources\StageResource\RelationManagers;

use App\Models\Split;
use App\Models\Stage;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SplitsRelationManager extends RelationManager
{
    protected static string $relationship = 'splits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('split_number')
                    ->label('Split Number')
                    ->placeholder('1')
                    ->integer()
                    ->required()
                    // todo switch other rule() functions that check the uniqueness of a number with this one
                    ->unique('splits', 'split_number', ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                        $stageId = $this->ownerRecord->id;
                        return $rule->where('stage_id', $stageId);
                    }),

                Forms\Components\TextInput::make('split_distance')
                    ->label('Split Distance')
                    ->placeholder('3.8')
                    ->numeric()
                    ->required()
                    ->rule(function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            $stageId = $this->ownerRecord->id;
                            $currentSplitNumber = $get('split_number');

                            $previousSplit = Split::where('stage_id', $stageId)
                                ->where('split_number', '<', $currentSplitNumber)
                                ->orderBy('split_number', 'desc')
                                ->first();


                            $nextSplit = Split::where('stage_id', $stageId)
                                ->where('split_number', '>', $currentSplitNumber)
                                ->orderBy('split_number', 'asc')
                                ->first();

                            $stageDistance = $this->ownerRecord->distance_km;

                            if ($previousSplit && $value <= $previousSplit->split_distance) {
                                $fail("The split distance must be greater than the previous split distance of {$previousSplit->split_distance} km.");
                            }

                            if ($nextSplit && $value >= $nextSplit->split_distance) {
                                $fail("The split distance must be less than the next split distance of {$nextSplit->split_distance} km.");
                            }

                            if ($value > $stageDistance) {
                                $fail("The split distance cannot exceed the stage's total distance of {$stageDistance} km.");
                            }
                        };
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Split')
            ->columns([
                Tables\Columns\TextColumn::make('split_number')->label('Split #')->prefix('#'),
                Tables\Columns\TextColumn::make('split_distance')->label('Split Position from Start')->suffix(' km')->grow(),
            ])
            ->defaultSort('split_number')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Split'),
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
