<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'photo',
        'description',
    ];

    public function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, 'anime_character')
            ->withTimestamps();
    }

    public function voiceActors(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'character_person')
            ->withPivot('anime_id')
            ->withTimestamps();
    }
}