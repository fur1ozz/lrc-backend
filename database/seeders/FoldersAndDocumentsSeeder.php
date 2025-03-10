<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Folder;
use App\Models\Document;
use App\Models\Rally;
use Illuminate\Support\Facades\DB;

class FoldersAndDocumentsSeeder extends Seeder
{
    public function run()
    {
        $rallies = Rally::all();

        $foldersData = [
            [
                "number" => "1",
                "title" => "Nolikumi un Biļeteni / Regulations and Bulletins",
                "files" => [
                    "Supplementary Regulations",
                    "Rally Guidelines",
                    "Itinerary Overview",
                ]
            ],
            [
                "number" => "2",
                "title" => "Komisāru dokumenti / Stewards documents",
                "files" => [
                    "Stewards Report No.1",
                    "Decision Report - Crew 24",
                ]
            ],
            [
                "number" => "3",
                "title" => "Sacensību vadītāja dokumenti / CoC documents",
                "files" => [
                    "Race Director's Notes No.2",
                    "Official Rally Update No.3",
                    "Safety Instructions",
                    "Communication Bulletin",
                    "Final Event Summary",
                ]
            ]
        ];

        foreach ($rallies as $rally) {
            foreach ($foldersData as $folderData) {
                $folder = Folder::create([
                    'rally_id' => $rally->id,
                    'number' => $folderData['number'],
                    'title' => $folderData['title']
                ]);

                foreach ($folderData['files'] as $index => $fileName) {
                    Document::create([
                        'folder_id' => $folder->id,
                        'name' => $fileName,
                        'link' => "https://example.com/document-{$rally->id}-{$folder->number}-" . ($index + 1) . ".pdf",
                    ]);
                }
            }
        }
    }
}
