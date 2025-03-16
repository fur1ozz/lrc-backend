<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    public function getTitle(): string
    {
        return "News Articles";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Article'),
        ];
    }
}
