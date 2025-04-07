<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $fillable = ['name', 'image', 'url'];

    public function rallies()
    {
        return $this->belongsToMany(Rally::class, 'rally_sponsor');
    }
}
