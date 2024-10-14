<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'co_driver_id',
        'team_id',
        'rally_id',
        'crew_number',
        'car',
        'drive_type',
        'drive_class',
    ];

    public function driver()
    {
        return $this->belongsTo(Participant::class, 'driver_id');
    }
    public function coDriver()
    {
        return $this->belongsTo(Participant::class, 'co_driver_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function rally()
    {
        return $this->belongsTo(Rally::class, 'rally_id');
    }
}

