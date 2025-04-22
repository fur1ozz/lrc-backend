<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected $fillable = ['year'];

    public function championshipClasses()
    {
        return $this->belongsToMany(GroupClass::class, 'championship_classes', 'season_id', 'class_id');
    }
}
