<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'pub_date_time',
        'title',
        'paragraph',
        'img_src',
        'rally_id'
    ];

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }
}

