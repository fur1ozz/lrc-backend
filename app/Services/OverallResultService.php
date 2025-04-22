<?php

namespace App\Services;
namespace App\Services;

use App\Models\Crew;
use App\Models\OverallResult;
use App\Models\Penalties;
use App\Models\Rally;
use App\Models\Retirement;
use App\Models\StageResults;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OverallResultService
{
    public function updateOrCreate(int $rallyId): void
    {
        DB::beginTransaction();

        try {
            $rally = Rally::findOrFail($rallyId);

            $crews = Crew::where('rally_id', $rally->id)->get();

            foreach ($crews as $crew) {
                $retirement = Retirement::where('crew_id', $crew->id)
                    ->where('rally_id', $rally->id)
                    ->first();

                if ($retirement) {
                    continue;
                }

                $stageResults = StageResults::where('crew_id', $crew->id)->get();

                $totalTime = 0;

                foreach ($stageResults as $stageResult) {
                    $totalTime += $stageResult->time_taken;
                }

                $penalties = Penalties::where('crew_id', $crew->id)->get();

                foreach ($penalties as $penalty) {
                    $totalTime += $penalty->penalty_amount;
                }

                OverallResult::updateOrCreate(
                    ['crew_id' => $crew->id, 'rally_id' => $rally->id],
                    ['total_time' => $totalTime]
                );
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating overall results for rally ID ' . $rallyId . ': ' . $e->getMessage());

            throw $e;
        }
    }
}
