<?php

namespace App\Filament\Resources\StageResource\Pages;

use App\Filament\Resources\StageResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

    public function getTitle(): string
    {
        return "Edit Stage - \"{$this->record->stage_name}\"";
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
