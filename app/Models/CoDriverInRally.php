<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoDriverInRally extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'co_driver_id',
        'rally_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Participant::class);
    }

    public function coDriver()
    {
        return $this->belongsTo(Participant::class, 'co_driver_id');
    }

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }
}
