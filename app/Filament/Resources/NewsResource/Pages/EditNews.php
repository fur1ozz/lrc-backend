<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    public function getTitle(): string
    {
        return "Edit Article - \"{$this->record->title}\"";
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
