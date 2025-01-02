<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Split extends Model
{
    use HasFactory;

    protected $table = 'splits';

    protected $fillable = [
        'stage_id',
        'split_number',
        'split_distance',
    ];
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

}
