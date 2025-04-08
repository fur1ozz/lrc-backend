<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChampionshipClass extends Model
{
    protected $fillable = [
        'season_id',
        'class_id',
    ];


    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function class()
    {
        return $this->belongsTo(GroupClass::class, 'class_id');
    }
}
