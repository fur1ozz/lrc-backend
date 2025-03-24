<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StageResults extends Model
{
    use HasFactory;
    protected $table = 'stage_results';

    protected $fillable = [
        'crew_id',
        'stage_id',
        'time_taken',
        'avg_speed'
    ];

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
}
