<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnimeSeason extends Model
{
    use HasFactory;

    protected $fillable = [
        'anime_id',
        'number',
        'title',
        'year',
    ];

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->title ?: "Temporada {$this->number}";
    }
}