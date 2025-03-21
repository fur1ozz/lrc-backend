<?php

namespace App\Filament\Resources\PrevWinnerResource\Pages;

use App\Filament\Resources\PrevWinnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListPrevWinners extends ListRecords
{
    protected static string $resource = PrevWinnerResource::class;

    public function getTitle(): string
    {
        return "Previous Rally Winners";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New Winner'),
        ];
    }
}
