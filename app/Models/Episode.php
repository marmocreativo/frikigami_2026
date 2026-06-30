<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'anime_season_id',
        'user_id',
        'number',
        'title',
        'synopsis',
        'air_date',
    ];

    protected $casts = [
        'air_date' => 'date',
    ];

    public function animeSeason(): BelongsTo
    {
        return $this->belongsTo(AnimeSeason::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}