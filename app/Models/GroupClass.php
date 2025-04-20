<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_name',
        'group_id',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function rallies()
    {
        return $this->belongsToMany(Rally::class, 'rally_classes', 'class_id', 'rally_id');
    }
}
