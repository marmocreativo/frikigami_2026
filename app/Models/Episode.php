<?php

namespace App\Models;

use App\Models\Concerns\HasRatings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Episode extends Model
{
    use HasFactory, HasRatings;

    protected $fillable = [
        'anime_season_id',
        'user_id',
        'number',
        'title',
        'synopsis',
        'thumbnail',
        'air_date',
    ];

    protected $casts = [
        'air_date' => 'date',
    ];

    /** Imagen previa del episodio, o null si no tiene. */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? Storage::url($this->thumbnail) : null;
    }

    public function animeSeason(): BelongsTo
    {
        return $this->belongsTo(AnimeSeason::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}