<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\GroupClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RallyClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'rallyClasses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('class_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('class_name')
            ->columns([
                Tables\Columns\TextColumn::make('class_name')
                    ->badge()
                    ->icon(fn ($record) => match ($record->class_name) {
                        'Jun' => 'heroicon-o-academic-cap',
                        default => null,
                    })
                    ->color(fn ($record) => $record->class_name === 'Jun' ? 'info' : Color::Orange),
            ])
            ->defaultSort('class_id')
            ->defaultGroup(
                Group::make('group_id')
                    ->label('Group')
                    ->getTitleFromRecordUsing(fn (Model $record) => $record->group->group_name)
                    ->collapsible()
            )
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('class_id')
                            ->label('Select a Class')
                            ->options(function ($state) {
                                $attachedClasses = $this->ownerRecord->rallyClasses->pluck('id');

                                $classes = GroupClass::whereNotIn('id', $attachedClasses)
                                    ->with('group')
                                    ->get()
                                    ->groupBy(function ($class) {
                                        return $class->group->group_name;
                                    });

                                return $classes->mapWithKeys(function ($groupClasses, $groupName) {
                                    return [
                                        $groupName => $groupClasses->mapWithKeys(function ($class) {
                                            return [$class->id => $class->class_name];
                                        })
                                    ];
                                });
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Only Class not yet added to this rally are listed.'),
                    ])
                    ->action(function (array $data) {
                        $ownerRecord = $this->ownerRecord;
                        $ownerRecord->rallyClasses()->attach($data['class_id']);
                    })
                    ->label('Add Class')
                    ->color('primary')
                    ->modalHeading('Add a Class to This Rally')
                    ->modalSubmitActionLabel('Add'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make('Remove Selected'),
            ]);
    }
}
