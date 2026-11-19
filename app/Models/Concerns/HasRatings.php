<?php

namespace App\Models\Concerns;

use App\Models\Comment;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Calificación de 1 a 5 estrellas y comentarios (animes y episodios).
 */
trait HasRatings
{
    /** Estrellas que se muestran cuando todavía nadie ha calificado. */
    public const DEFAULT_STARS = 3;

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /** Promedio real de las calificaciones, o null si no hay. */
    public function averageRating(): ?float
    {
        $count = $this->ratings()->count();

        return $count ? round((float) $this->ratings()->avg('stars'), 1) : null;
    }

    /** Lo que se muestra en pantalla: el promedio, o 3 estrellas por defecto. */
    public function displayRating(): float
    {
        return $this->averageRating() ?? (float) self::DEFAULT_STARS;
    }
}
