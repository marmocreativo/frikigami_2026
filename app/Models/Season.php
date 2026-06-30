<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
    ];

    public function animes(): HasMany
    {
        return $this->hasMany(Anime::class);
    }

    public function getLabelAttribute(): string
    {
        return "{$this->name} {$this->year}";
    }
}