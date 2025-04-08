<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChampionshipPoint extends Model
{
    protected $fillable = [
        'season_id',
        'class_id',
        'crew_id',
        'driver_id',
        'points',
        'power_stage',
        'position',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function class()
    {
        return $this->belongsTo(GroupClass::class, 'class_id');
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function driver()
    {
        return $this->belongsTo(Participant::class, 'driver_id');
    }
}
