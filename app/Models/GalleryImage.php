<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'rally_id',
        'img_src',
        'position',
        'created_by',
    ];

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }
}
