<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
    ];

    public function classes()
    {
        return $this->hasMany(GroupClass::class);
    }

    public function crews()
    {
        return $this->belongsToMany(Crew::class, 'crew_group_involvements', 'group_id', 'crew_id')
            ->withTimestamps();
    }
}
