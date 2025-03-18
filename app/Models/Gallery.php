<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'rally_id',
        'img_src',
    ];

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }
}
