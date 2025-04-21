<?php

namespace App\Filament\Resources\RallyResource\Pages;

use App\Filament\Resources\RallyResource;
use App\Models\OverallResult;
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

        $resultsAction = $hasStarted
            ? Action::make('Calculate Overall Results')
                ->label($existingResults ? 'Recalculate Results' : 'Calculate Results')
                ->color('primary')
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

        return [
            $this->getSaveFormAction()->formId('form'),
            $this->getCancelFormAction()->formId('form'),
            $resultsAction,
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->color(Color::Sky);
    }
}
