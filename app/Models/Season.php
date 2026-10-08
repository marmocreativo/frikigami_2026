<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
    
    public function getSlugAttribute(): string
    {
        return Str::slug($this->name) . '-' . $this->year; // otono-2025
    }

    public static function fromSlug(string $slug): ?self
    {
        $names = [
            'invierno' => 'Invierno',
            'primavera' => 'Primavera',
            'verano' => 'Verano',
            'otono' => 'Otoño',
        ];

        if (! preg_match('/^(invierno|primavera|verano|otono)-(\d{4})$/', $slug, $m)) {
            return null;
        }

        return static::where('name', $names[$m[1]])->where('year', $m[2])->first();
    }
}