<?php

namespace App\Filament\Resources\RallyResource\Pages;

use App\Filament\Resources\RallyResource;
use App\Models\ChampionshipPoint;
use App\Models\OverallResult;
use App\Services\ChampionshipPointService;
use App\Services\OverallResultService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditRally extends EditRecord
{
    protected static string $resource = RallyResource::class;

    public function getTitle(): string
    {
        return "Edit Rally - {$this->record->rally_name} {$this->record->season->year}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getFormActions(): array
    {
        $existingResults = OverallResult::where('rally_id', $this->record->id)->exists();
        $hasStarted = now()->greaterThanOrEqualTo($this->record->date_from);

        $rallyCrews = $this->record->crews->pluck('id')->toArray();
        $existingPoints = ChampionshipPoint::whereIn("crew_id", $rallyCrews)->exists();
        $hasFinished = now()->greaterThanOrEqualTo($this->record->date_to);

        $resultsAction = $hasStarted
            ? Action::make('Calculate Overall Results')
                ->label($existingResults ? 'Recalculate Results' : 'Calculate Results')
                ->color('primary')
                ->tooltip($existingResults ? 'Recalculate Overall Results' : 'Calculate Overall Results')
                ->icon($existingResults ? 'heroicon-o-arrow-path' : 'heroicon-o-calculator')
                ->requiresConfirmation()
                ->extraAttributes([
                    'class' => 'ml-auto',
                ])
                ->action(function () use ($existingResults) {
                    $rally = $this->record;

                    try {
                        app(OverallResultService::class)->updateOrCreate($rally->id);

                        Notification::make()
                            ->title('Success')
                            ->body('Overall results have been ' . ($existingResults ? 'recalculated' : 'calculated') . '.')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Something went wrong: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
            : Action::make('Unavailable')
                ->label('Results calculation unavailable')
                ->icon('heroicon-o-lock-closed')
                ->color('gray')
                ->disabled()
                ->extraAttributes([
                    'class' => 'ml-auto',
                ]);

        $pointsAction = $hasFinished
            ? ($existingResults
                ? Action::make('Calculate Championship Points')
                    ->label($existingPoints ? 'Recalculate Points' : 'Calculate Points')
                    ->color(Color::Teal)
                    ->tooltip($existingPoints ? 'Recalculate Championship Points' : 'Calculate Championship Points')
                    ->icon($existingPoints ? 'heroicon-o-arrow-path' : 'heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->action(function () use ($existingPoints) {
                        $rally = $this->record;

                        try {
                            app(ChampionshipPointService::class)->calculatePointsForRally($rally->id);

                            Notification::make()
                                ->title('Success')
                                ->body('Points have been ' . ($existingPoints ? 'recalculated' : 'calculated') . '.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Something went wrong: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                : Action::make('Unavailable')
                    ->label('Calculate Overall Results First')
                    ->color('gray')
                    ->icon('heroicon-o-lock-closed')
                    ->disabled()
            )
            : null;


        $formActions = [
            $this->getSaveFormAction()->formId('form'),
            $this->getCancelFormAction()->formId('form'),
            $resultsAction,
        ];

        if ($hasFinished) {
            $formActions[] = $pointsAction;
        }

        return $formActions;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->color(Color::Sky);
    }
}
