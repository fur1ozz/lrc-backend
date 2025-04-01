<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StartTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'crew_id',
        'stage_id',
        'start_time'
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
