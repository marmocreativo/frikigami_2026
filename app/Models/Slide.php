<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Slide del carrusel del inicio.
 */
class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'anime_id',
        'link',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::url($this->image);
    }

    /** Link explícito, o la ficha del anime relacionado, o nada. */
    public function getTargetUrlAttribute(): ?string
    {
        return $this->link ?: ($this->anime ? route('animes.show', $this->anime) : null);
    }
}
