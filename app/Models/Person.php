<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'photo',
        'bio',
        'is_voice_actor',
        'is_staff',
    ];

    protected $casts = [
        'is_voice_actor' => 'boolean',
        'is_staff' => 'boolean',
    ];

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_person')
            ->withPivot('anime_id')
            ->withTimestamps();
    }

    public function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, 'anime_person')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}