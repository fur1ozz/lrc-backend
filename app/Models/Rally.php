<?php
namespace App\Models;

use App\Enums\RoadSurfaceEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    protected $casts = [
        'road_surface' => RoadSurfaceEnum::class,
    ];

    protected static function booted(): void
    {
        static::creating(function ($rally) {
            // Auto-generate the rally_tag based on the rally_name
            if (empty($rally->rally_tag) && !empty($rally->rally_name)) {
                $state = str_ireplace('Rallysprint', 'rally-sprint', $rally->rally_name);
                $state = ucwords(strtolower($state));

                $rally->rally_tag = Str::slug($state);
            }

            // Auto-generate the rally_sequence for the specified season_id
            if (empty($rally->rally_sequence) && !empty($rally->season_id)) {
                $rally->rally_sequence = Rally::where('season_id', $rally->season_id)
                        ->max('rally_sequence') + 1; // Get the next sequence number
            }
        });
    }

    /**
     * Get the season that this rally belongs to.
     */
    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
}
