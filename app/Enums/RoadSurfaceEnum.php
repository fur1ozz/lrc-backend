<?php

namespace App\Enums;

enum RoadSurfaceEnum : string
{
    case GRAVEL = 'gravel';
    case SNOW = 'snow';
    case TARMAC = 'tarmac';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
