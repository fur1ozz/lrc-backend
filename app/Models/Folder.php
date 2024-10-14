<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'rally_id',
        'number',
        'title',
    ];

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}

