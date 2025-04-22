<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\GroupClass;
use App\Models\RallyClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RallyClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'rallyClasses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                            ->label('Select Classes')
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
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->helperText('Select multiple classes to add to this rally.')
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $rallyId = $livewire->getOwnerRecord()->id;
                        $addedCount = 0;

                        foreach ($data['class_id'] as $classId) {
                            $addedCount++;
                            RallyClass::create([
                                'rally_id' => $rallyId,
                                'class_id' => $classId,
                            ]);
                        }

                        Notification::make()
                            ->title("{$addedCount} Class" . ($addedCount > 1 ? 'es' : '') . ' Added')
                            ->success()
                            ->send();
                    })
                    ->label('Add Classes')
                    ->color('primary')
                    ->modalHeading('Add Classes to This Rally')
                    ->modalSubmitActionLabel('Add'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove')
                    ->action(function (Model $record, RelationManager $livewire) {
                        RallyClass::where('rally_id', $livewire->getOwnerRecord()->id)
                            ->where('class_id', $record->id)
                            ->first()?->delete();

                        Notification::make()
                            ->title('Class Removed')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Remove Selected')
                    ->action(function (Collection $records, RelationManager $livewire) {
                        $removedCount = 0;

                        foreach ($records as $record) {
                            $removedCount++;
                            RallyClass::where('rally_id', $livewire->getOwnerRecord()->id)
                                ->where('class_id', $record->id)
                                ->first()?->delete();
                        }

                        Notification::make()
                            ->title("{$removedCount} Class" . ($removedCount > 1 ? 'es' : '') . ' Removed')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
