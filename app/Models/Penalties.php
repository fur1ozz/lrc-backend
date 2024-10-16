<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalties extends Model
{
    use HasFactory;

    protected $table = 'penalties';

    protected $fillable = [
        'crew_id',
        'stage_id',
        'penalty_type',
        'penalty_amount'
    ];

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
}
