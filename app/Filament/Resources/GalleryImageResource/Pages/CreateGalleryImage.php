<?php

namespace App\Filament\Resources\GalleryImageResource\Pages;

use App\Filament\Resources\GalleryImageResource;
use App\Models\GalleryImage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGalleryImage extends CreateRecord
{
    protected static string $resource = GalleryImageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $images = $data['img_src'] ?? [];

        foreach ($images as $image) {
            GalleryImage::create([
                'img_src' => $image,
                'rally_id' => $data['rally_id'],
                'season_id' => $data['season_id'],
                'created_by' => $data['created_by'],
            ]);
        }

        return new GalleryImage();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
