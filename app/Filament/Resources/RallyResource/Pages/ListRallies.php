<?php

namespace App\Filament\Resources\RallyResource\Pages;

use App\Filament\Resources\RallyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRallies extends ListRecords
{
    protected static string $resource = RallyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
