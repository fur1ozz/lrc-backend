<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RallyGroup extends Model
{
    protected $fillable = [
        'rally_id',
        'group_id',
    ];

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
