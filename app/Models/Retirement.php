<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retirement extends Model
{
    use HasFactory;
    protected $fillable = [
        'crew_id',
        'rally_id',
        'retirement_reason',
        'stage_of_retirement',
    ];

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }
}
