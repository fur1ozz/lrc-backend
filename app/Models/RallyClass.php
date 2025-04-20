<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RallyClass extends Model
{
    protected $fillable = [
        'rally_id',
        'class_id',
    ];

    protected static function booted()
    {
        static::created(function (RallyClass $rallyClass) {
            $group = $rallyClass->class->group;

            if (!$group) {
                return;
            }

            $alreadyExists = \App\Models\RallyGroup::where('rally_id', $rallyClass->rally_id)
                ->where('group_id', $group->id)
                ->exists();

            if (!$alreadyExists) {
                \App\Models\RallyGroup::create([
                    'rally_id' => $rallyClass->rally_id,
                    'group_id' => $group->id,
                ]);
            }
        });

        static::deleting(function (RallyClass $rallyClass) {
            $rally = $rallyClass->rally;
            $group = $rallyClass->class->group;

            if (!$group) {
                return;
            }

            $remainingClassesInGroup = \App\Models\RallyClass::where('rally_id', $rally->id)
                ->whereHas('class', fn ($query) => $query->where('group_id', $group->id))
                ->where('class_id', '!=', $rallyClass->class_id)
                ->exists();

            if (!$remainingClassesInGroup) {
                \App\Models\RallyGroup::where('rally_id', $rally->id)
                    ->where('group_id', $group->id)
                    ->delete();
            }
        });
    }

    public function rally()
    {
        return $this->belongsTo(Rally::class);
    }

    public function class()
    {
        return $this->belongsTo(GroupClass::class, 'class_id');
    }
}
