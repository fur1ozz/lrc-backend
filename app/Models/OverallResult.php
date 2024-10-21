<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverallResult extends Model
{
    use HasFactory;

    protected $table = 'overall_results';

    protected $fillable = [
        'crew_id',
        'rally_id',
        'total_time',
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
