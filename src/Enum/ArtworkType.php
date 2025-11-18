<?php

namespace App\Enum;

enum ArtworkType: string
{
    case PAINTING = 'painting';
    case SCULPTURE = 'sculpture';
    case DRAWING = 'drawing';
    case PHOTOGRAPHY = 'photography';
}