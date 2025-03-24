<?php

namespace App\Filament\Resources\PrevWinnerResource\Pages;

use App\Filament\Resources\PrevWinnerResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditPrevWinner extends EditRecord
{
    protected static string $resource = PrevWinnerResource::class;

    public function getTitle(): string
    {
        return "Edit Winner for - {$this->record->rally->rally_name} {$this->record->rally->season->year}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->color(Color::Sky);
    }
}
