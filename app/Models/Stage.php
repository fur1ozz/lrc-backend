<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'rally_id',
        'stage_name',
        'stage_number',
        'distance_km',
        'start_date',
        'start_time',
    ];
    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }

    public function splits()
    {
        return $this->hasMany(Split::class);
    }

    public function startTimes()
    {
        return $this->hasMany(StartTime::class);
    }
}

