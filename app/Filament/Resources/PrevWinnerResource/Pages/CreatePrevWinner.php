<?php

namespace App\Filament\Resources\PrevWinnerResource\Pages;

use App\Filament\Resources\PrevWinnerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePrevWinner extends CreateRecord
{
    protected static string $resource = PrevWinnerResource::class;

    public function getTitle(): string
    {
        return "Create New Winner";
    }
}
