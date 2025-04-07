<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RallySponsor extends Model
{
    protected $table = 'rally_sponsor';

    protected $fillable = ['rally_id', 'sponsor_id'];

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
