<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rally extends Model
{
    use HasFactory;

    protected $fillable = [
        'rally_name',
        'date_from',
        'date_to',
        'location',
        'road_surface',
        'rally_tag',
        'season_id',
        'rally_sequence',
    ];

    /**
     * Get the season that this rally belongs to.
     */
    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
