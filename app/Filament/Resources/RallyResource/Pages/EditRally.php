<?php

namespace App\Filament\Resources\RallyResource\Pages;

use App\Filament\Resources\RallyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
}
