<?php

namespace App\Models;

use App\Enums\DriveTypeEnum;
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
        'crew_number_int',
        'is_historic',
        'car',
        'drive_type',
        'drive_class',
    ];

    protected $casts = [
        'drive_type' => DriveTypeEnum::class,
    ];

    // Accessor for `crew_number`
    public function getCrewNumberAttribute() {
        return $this->is_historic ? 'H' . $this->crew_number_int : (string) $this->crew_number_int;
    }

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
    public function stageResults()
    {
        return $this->hasMany(StageResults::class, 'crew_id');
    }

    public function classes()
    {
        return $this->belongsToMany(GroupClass::class, 'crew_class_involvements', 'crew_id', 'class_id')
            ->withTimestamps();
    }
}

