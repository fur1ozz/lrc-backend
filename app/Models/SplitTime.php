<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplitTime extends Model
{
    use HasFactory;

    protected $table = 'split_times';

    protected $fillable = [
        'crew_id',
        'split_id',
        'split_time',
    ];

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }

    public function split()
    {
        return $this->belongsTo(Split::class);
    }
}
