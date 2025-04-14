<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RallyClass extends Model
{
    protected $fillable = [
        'rally_id',
        'class_id',
    ];


    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }

    public function class()
    {
        return $this->belongsTo(GroupClass::class, 'class_id');
    }
}
