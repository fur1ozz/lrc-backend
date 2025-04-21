<?php

namespace App\Filament\Resources\RallyResource\RelationManagers;

use App\Models\Crew;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class RallyWinnerRelationManager extends RelationManager
{
    protected static string $relationship = 'rallyWinner';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Select::make('crew_id')
                            ->label('Crew')
                            ->options(fn () => Crew::where('rally_id', $this->getOwnerRecord()->id)
                                ->with(['driver', 'coDriver'])
                                ->orderByRaw('is_historic ASC, crew_number_int ASC')
                                ->get()
                                ->mapWithKeys(fn ($crew) => [
                                    $crew->id => "{$crew->driver?->name} {$crew->driver?->surname} / {$crew->coDriver?->name} {$crew->coDriver?->surname} (Car: {$crew->car}, No: {$crew->crew_number})"
                                ])
                                ->toArray())
                            ->searchable()
                            ->required()
                            ->placeholder('Select a crew')
                            ->helperText('Select a crew from this rally')
                            ->rule('exists:crews,id'),

                        Forms\Components\Textarea::make('feedback')
                            ->required()
                            ->helperText('Crews feedback about the rally')
                            ->rows(3)
                            ->autosize()
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        FileUpload::make('winning_img')
                            ->label('Image')
                            ->image()
                            ->directory('rally_winners')
                            ->required()
                            ->openable()
                            ->downloadable()
                            ->uploadingMessage('Uploading image...')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('The image of the winning crew')
                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        $canCreateWinner = !$this->getOwnerRecord()->rallyWinner()->exists() && $this->getOwnerRecord()->date_to <= now();

        return $table
            ->recordTitle('Winner')
            ->columns([
                Tables\Columns\TextColumn::make('crew')
                    ->label('Driver & Co-Driver')
                    ->formatStateUsing(function ($record) {

                        $driver = $record->crew->driver;
                        $coDriver = $record->crew->coDriver;

                        return "{$driver->name} {$driver->surname} / {$coDriver->name} {$coDriver->surname}";
                    })
                    ->description(fn (Model $record): string => Str::limit('Car: '.$record->crew->car.', No: '.$record->crew->crew_number, 70)),

                Tables\Columns\TextColumn::make('feedback')
                    ->wrap()
                    ->grow(),

                Tables\Columns\ImageColumn::make('winning_img')
                    ->label('Image')
                    ->alignCenter()
                    ->square(),
            ])
            ->paginated(false)
            ->emptyStateHeading($canCreateWinner ? 'No winner set yet' : 'Rally has not ended')
            ->emptyStateDescription($canCreateWinner
                ? 'Set a winner for this rally to display their details.'
                : 'This rally has not ended and no winner can be set yet.')
            ->emptyStateActions(
                $canCreateWinner
                    ? [
                        Tables\Actions\CreateAction::make()
                            ->label('Set the Rally Winner'),
                    ]
                    : []
            )
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color(Color::Sky),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
