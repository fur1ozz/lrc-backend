<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrevWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'rally_id',
        'crew_id',
        'feedback',
        'winning_img'
    ];

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }
}
