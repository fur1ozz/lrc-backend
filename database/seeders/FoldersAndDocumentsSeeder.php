<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Folder;
use App\Models\Document;
use App\Models\Rally;

class FoldersAndDocumentsSeeder extends Seeder
{
    public function run()
    {
        $rallyId = 1;

        $data = [
            [
                "number" => "1",
                "title" => "Nolikumi un Biļeteni / Regulations and Bulletins",
                "files" => [
                    [
                        "name" => "Papildus nolikums",
                        "link" => "https://example.com/papildus-nolikums.pdf"
                    ],
                    [
                        "name" => "Supplementary Regulations",
                        "link" => "https://example.com/supplementary-regulations.pdf"
                    ],
                    [
                        "name" => "Pielikums Nr.1 - Maršruta karte | Appendix No.1 - Primary itinerary",
                        "link" => "https://example.com/appendix-no1-itinerary.pdf"
                    ]
                ]
            ],
            [
                "number" => "2",
                "title" => "Komisāru dokumenti / Stewards documents",
                "files" => [
                    [
                        "name" => "Stewards Decision No.1",
                        "link" => "https://example.com/stewards-decision-no1.pdf"
                    ],
                    [
                        "name" => "Stewards Decision No.2 - Crew 36",
                        "link" => "https://example.com/stewards-decision-no2-crew36.pdf"
                    ]
                ]
            ],
            [
                "number" => "3",
                "title" => "Sacensību vadītāja dokumenti/ CoC documents",
                "files" => [
                    [
                        "name" => "Komunikācija Nr.2 | Communication No2 - Rallysprint",
                        "link" => "https://app-cdn.sportity.com/51115f7b-c8e5-4371-8a2b-04897826994d/communication_no2_rallysprint.pdf"
                    ],
                    [
                        "name" => "Komunikācija Nr.3 | Communication No3",
                        "link" => "https://app-cdn.sportity.com/51115f7b-c8e5-4371-8a2b-04897826994d/communication_no3.pdf"
                    ],
                    [
                        "name" => "Komunikācija Nr.4 | Communication No4",
                        "link" => "https://app-cdn.sportity.com/51115f7b-c8e5-4371-8a2b-04897826994d/communication_no4.pdf"
                    ],
                    [
                        "name" => "Komunikācija Nr.5 | Communication No5",
                        "link" => "https://app-cdn.sportity.com/51115f7b-c8e5-4371-8a2b-04897826994d/communication_no5.pdf"
                    ],
                    [
                        "name" => "Komunikācija Nr.6 | Communication No6",
                        "link" => "https://app-cdn.sportity.com/51115f7b-c8e5-4371-8a2b-04897826994d/communication_no6.pdf"
                    ]
                ]
            ]
        ];

        foreach ($data as $folderData) {
            $folder = Folder::create([
                'rally_id' => $rallyId,
                'number' => $folderData['number'],
                'title' => $folderData['title']
            ]);

            foreach ($folderData['files'] as $file) {
                Document::create([
                    'folder_id' => $folder->id,
                    'name' => $file['name'],
                    'link' => $file['link']
                ]);
            }
        }
    }
}

