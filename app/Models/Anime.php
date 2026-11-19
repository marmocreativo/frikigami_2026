<?php

namespace App\Models;

use App\Models\Concerns\HasRatings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Anime extends Model
{
    use HasFactory, HasRatings;

    protected $fillable = [
        'title',
        'slug',
        'synopsis',
        'cover_image',
        'status',
        'season_id',
        'anilist_id',
        'mal_id',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function animeSeasons(): HasMany
    {
        return $this->hasMany(AnimeSeason::class);
    }

    public function episodes(): HasManyThrough
    {
        return $this->hasManyThrough(Episode::class, AnimeSeason::class);
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'anime_character')
            ->withTimestamps();
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'anime_person')
            ->withPivot('role')
            ->withTimestamps();
    }
    
    /** Actores de voz de este anime (tabla character_person, con anime_id). */
    public function voiceActors(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'character_person', 'anime_id', 'person_id')->distinct();
    }

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'anime_news')
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}