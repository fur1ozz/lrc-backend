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
        'rally_img',
        'rally_banner',
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

    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function galleryImages()
    {
        return $this->hasMany(GalleryImage::class);
    }

    public function rallyWinner()
    {
        return $this->hasOne(PrevWinner::class);
    }

    public function crews()
    {
        return $this->hasMany(Crew::class);
    }

    public function rallySponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'rally_sponsor')->withPivot('type');
    }

    public function rallyClasses()
    {
        return $this->belongsToMany(GroupClass::class, 'rally_classes', 'rally_id', 'class_id');
    }
}
