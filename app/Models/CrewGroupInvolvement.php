<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrewGroupInvolvement extends Model
{
    use HasFactory;

    protected $fillable = [
        'crew_id',
        'group_id',
    ];

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
