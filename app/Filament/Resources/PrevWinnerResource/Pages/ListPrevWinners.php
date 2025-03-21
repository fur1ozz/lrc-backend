<?php

namespace App\Filament\Resources\PrevWinnerResource\Pages;

use App\Filament\Resources\PrevWinnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrevWinners extends ListRecords
{
    protected static string $resource = PrevWinnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add New Winner'),
        ];
    }
}
