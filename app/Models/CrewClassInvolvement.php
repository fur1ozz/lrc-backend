<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrewClassInvolvement extends Model
{
    use HasFactory;

    protected $fillable = [
        'crew_id',
        'class_id',
    ];

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function groupClass()
    {
        return $this->belongsTo(GroupClass::class);
    }
}
