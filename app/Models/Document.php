<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'name',
        'link',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}

