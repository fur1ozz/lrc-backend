<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Enums\DriveTypeEnum;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrewsRelationManager extends RelationManager
{
    protected static string $relationship = 'crews';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('driver_id')
                    ->label('Driver')
                    ->relationship('driver', 'name', fn ($query) => $query->orderBy('surname'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} {$record->surname} ({$record->nationality}) - ID: {$record->id}")
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter first name'),

                        Forms\Components\TextInput::make('surname')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter last name'),

                        Forms\Components\TextInput::make('nationality')
                            ->required()
                            ->maxLength(2)
                            ->placeholder('e.g., LV')
                            ->helperText('Enter a 2-letter nationality code. Examples: LV, LT, EE, FI, SE'),
                    ])
                    ->required()
                    ->rules(function (Get $get) {
                        return [
                            function ($attribute, $value, Closure $fail) use ($get) {
                                $rally = $this->getOwnerRecord();
                                $crewId = $get('id');

                                if (! $rally || ! $value) return;

                                $existingCrew = $rally->crews()
                                    ->where(function ($query) use ($value) {
                                        $query->where('driver_id', $value)
                                            ->orWhere('co_driver_id', $value);
                                    })
                                    ->when($crewId, fn ($query) => $query->where('id', '!=', $crewId))
                                    ->first();

                                if ($existingCrew) {
                                    $fail("This participant is already assigned in Crew ID: {$existingCrew->id}.");
                                }
                            },
                        ];
                    })
                    ->helperText('Select an existing driver or create a new one. ⚠ Creating a new driver will affect championship standings, as points are calculated per driver.'),

                Forms\Components\Select::make('co_driver_id')
                    ->label('Co-Driver')
                    ->relationship('coDriver', 'name', fn ($query) => $query->orderBy('surname'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} {$record->surname} ({$record->nationality}) - ID: {$record->id}")
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter first name'),

                        Forms\Components\TextInput::make('surname')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter last name'),

                        Forms\Components\TextInput::make('nationality')
                            ->required()
                            ->maxLength(2)
                            ->placeholder('e.g., LV')
                            ->helperText('Enter a 2-letter nationality code. Examples: LV, LT, EE, FI, SE'),
                    ])
                    ->required()
                    ->helperText('Select an existing co-driver or create a new one. The co-driver cannot be the same person as the driver.')
                    ->rules(function (Get $get) {
                        return [
                            function ($attribute, $value, Closure $fail) use ($get) {
                                $driverId = $get('driver_id');
                                if ((int) $value === (int) $driverId) {
                                    $fail('The co-driver cannot be the same as the driver.');
                                    return;
                                }

                                $rally = $this->getOwnerRecord();
                                $crewId = $get('id');

                                if (! $rally || ! $value) return;

                                $existingCrew = $rally->crews()
                                    ->where(function ($query) use ($value) {
                                        $query->where('driver_id', $value)
                                            ->orWhere('co_driver_id', $value);
                                    })
                                    ->when($crewId, fn ($query) => $query->where('id', '!=', $crewId))
                                    ->first();

                                if ($existingCrew) {
                                    $fail("This participant is already assigned in Crew ID: {$existingCrew->id}.");
                                }
                            },
                        ];
                    }),

                Forms\Components\Select::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'team_name', fn ($query) => $query->orderBy('team_name'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->team_name} - ID: {$record->id}")
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('team_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter team name'),

                        Forms\Components\TextInput::make('manager_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter team manager\'s full name'),

                        Forms\Components\TextInput::make('manager_contact')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter manager contact info (phone or email)')
                            ->helperText('Example: +371 12345678 or manager@example.com'),
                    ])
                    ->required()
                    ->helperText('Select an existing team or create a new one'),

                Forms\Components\TextInput::make('car')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Mitsubishi Evo IX'),

                Forms\Components\TextInput::make('crew_number_int')
                    ->required()
                    ->numeric()
                    ->label('Crew Number')
                    ->rules(['max:300'])
                    ->helperText('Max value of crew number is 300')
                    ->rules([
                        function (callable $get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                $rally = $this->getOwnerRecord();
                                $isHistoric = $get('is_historic');
                                $crewId = $get('id');

                                if (! $rally || $value === null) return;

                                $exists = $rally->crews()
                                    ->where('crew_number_int', $value)
                                    ->where('is_historic', $isHistoric)
                                    ->when($crewId, fn ($query) => $query->where('id', '!=', $crewId))
                                    ->exists();

                                if ($exists) {
                                    $fail("Crew number {$value} is already used by Crew ID: {$crewId}");
                                }
                            };
                        },
                    ]),

                Forms\Components\Toggle::make('is_historic')
                    ->label('Historic')
                    ->onColor('warning')
                    ->inline(false)
                    ->helperText('Determines if the crew participates in Historic category'),

                Forms\Components\ToggleButtons::make('drive_type')
                    ->options([
                        DriveTypeEnum::AWD->value => 'AWD',
                        DriveTypeEnum::RWD->value => 'RWD',
                        DriveTypeEnum::FWD->value => 'FWD',
                    ])
                    ->colors([
                        DriveTypeEnum::AWD->value => Color::Blue,
                        DriveTypeEnum::RWD->value => Color::Amber,
                        DriveTypeEnum::FWD->value => Color::Lime,
                    ])
                    ->inline()
                    ->required()
                    ->label('Drive Type'),

                Forms\Components\Select::make('drive_class')
                    ->label('Drive Class')
                    ->options(function (RelationManager $livewire) {
                        $rally = $livewire->getOwnerRecord();
                        if (! $rally) {
                            return [];
                        }

                        $rallyClasses = \App\Models\RallyClass::query()
                            ->where('rally_id', $rally->id)
                            ->with('class.group')
                            ->get();

                        $grouped = $rallyClasses->groupBy(fn ($rallyClass) => $rallyClass->class->group->group_name ?? 'Other');

                        return $grouped->mapWithKeys(function ($groupClasses, $groupName) {
                            return [
                                $groupName => $groupClasses->mapWithKeys(function ($rallyClass) {
                                    $className = $rallyClass->class->class_name;
                                    return [$className => $className];
                                }),
                            ];
                        });
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Select the main class this crew participates in.'),

                Forms\Components\Select::make('classes')
                    ->label('Additional Classes')
                    ->multiple()
                    ->relationship('classes', 'class_name')
                    ->options(function (RelationManager $livewire) {
                        $rally = $livewire->getOwnerRecord();
                        if (! $rally) return [];

                        $rallyClasses = \App\Models\RallyClass::with('class.group')
                            ->where('rally_id', $rally->id)
                            ->get();

                        return $rallyClasses->groupBy(fn ($rc) => $rc->class->group->group_name ?? 'Other')
                            ->mapWithKeys(function ($grouped, $groupName) {
                                return [
                                    $groupName => $grouped->mapWithKeys(function ($rallyClass) {
                                        $class = $rallyClass->class;
                                        return [$class->id => $class->class_name];
                                    }),
                                ];
                            });
                    })
                    ->helperText('Select the classes this crew will participate in')
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Crew')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Crew ID')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('driver_and_codriver')
                    ->label('Driver & Co-Driver')
                    ->state(function (Model $record) {
                        $driver = $record->driver;
                        $coDriver = $record->coDriver;

                        return "{$driver->name} {$driver->surname} / {$coDriver->name} {$coDriver->surname}";
                    })
                    ->description(fn (Model $record): string => Str::limit('Car: '.$record->car, 70)),

                Tables\Columns\TextColumn::make('team.team_name')
                    ->label('Team Name'),

                Tables\Columns\TextColumn::make('crew_number')
                    ->label('No:')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_historic')
                    ->label('Historic')
                    ->alignCenter()
                    ->trueColor('warning')
                    ->falseColor('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('drive_type')
                    ->label('Drive Type')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (DriveTypeEnum $state): array => match ($state) {
                        DriveTypeEnum::AWD => Color::Blue,
                        DriveTypeEnum::RWD => Color::Amber,
                        DriveTypeEnum::FWD => Color::Lime,
                    }),

                Tables\Columns\TextColumn::make('drive_class')
                    ->label('Main Drive Class')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('classes.class_name')
                    ->label('All Classes')
                    ->formatStateUsing(fn ($state, Model $record) => $record->classes->pluck('class_name')->join(', '))
                    ->wrap()
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->toggleable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_historic')
                    ->label('Class Category')
                    ->options([
                        '1' => 'Historic',
                        '0' => 'Non-Historic',
                    ]),
                Tables\Filters\Filter::make('driver_search')
                    ->label('Driver / Co-Driver')
                    ->form([
                        Forms\Components\TextInput::make('full_name')
                            ->placeholder('Enter full name...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!filled($data['full_name'])) {
                            return $query;
                        }

                        $search = '%' . $data['full_name'] . '%';

                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('driver', function ($driverQuery) use ($search) {
                                $driverQuery->whereRaw("CONCAT(name, ' ', surname) LIKE ?", [$search])
                                    ->orWhereRaw("CONCAT(surname, ' ', name) LIKE ?", [$search]);
                            })->orWhereHas('coDriver', function ($coDriverQuery) use ($search) {
                                $coDriverQuery->whereRaw("CONCAT(name, ' ', surname) LIKE ?", [$search])
                                    ->orWhereRaw("CONCAT(surname, ' ', name) LIKE ?", [$search]);
                            });
                        });
                    }),

            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->toggleColumnsTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Toggle Columns'),
            )
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Crew')
                    ->after(function (Model $record) {
                        $this->syncGroupInvolvements($record);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color(Color::Sky)
                    ->after(function (Model $record) {
                        $this->syncGroupInvolvements($record);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected function syncGroupInvolvements(Model $crew): void
    {

        $attachedClasses = $crew->classes()->with('group')->get();

        $groupIds = $attachedClasses
            ->pluck('group_id')
            ->filter()
            ->unique()
            ->values();

        $crew->crewGroups()
            ->whereNotIn('group_id', $groupIds)
            ->delete();

        foreach ($groupIds as $groupId) {
            \App\Models\CrewGroupInvolvement::firstOrCreate([
                'crew_id' => $crew->id,
                'group_id' => $groupId,
            ]);
        }
    }
}
