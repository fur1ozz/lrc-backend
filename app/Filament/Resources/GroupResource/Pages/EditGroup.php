<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    public function getTitle(): string
    {
        return "Edit Group - {$this->record->group_name}";
    }
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->color(Color::Sky);
    }
}
