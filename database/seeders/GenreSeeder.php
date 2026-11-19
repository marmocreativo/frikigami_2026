<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Géneros oficiales de AniList (GenreCollection), con nombre en español.
 * `anilist_name` guarda el nombre original para emparejar al importar.
 *
 * Se omite "Hentai": el contenido adulto no se importa (isAdult: false).
 * Es seguro correrlo varias veces: actualiza en vez de duplicar.
 */
class GenreSeeder extends Seeder
{
    public const ANILIST = [
        'Action' => 'Acción',
        'Adventure' => 'Aventura',
        'Comedy' => 'Comedia',
        'Drama' => 'Drama',
        'Ecchi' => 'Ecchi',
        'Fantasy' => 'Fantasía',
        'Horror' => 'Terror',
        'Mahou Shoujo' => 'Chicas mágicas',
        'Mecha' => 'Mecha',
        'Music' => 'Música',
        'Mystery' => 'Misterio',
        'Psychological' => 'Psicológico',
        'Romance' => 'Romance',
        'Sci-Fi' => 'Ciencia ficción',
        'Slice of Life' => 'Slice of Life',
        'Sports' => 'Deportes',
        'Supernatural' => 'Sobrenatural',
        'Thriller' => 'Suspenso',
    ];

    public function run(): void
    {
        foreach (self::ANILIST as $anilistName => $name) {
            $slug = Str::slug($name);

            // Si ya existía (por AniList o por slug, ej. creado a mano), se reutiliza
            $genre = Genre::where('anilist_name', $anilistName)->orWhere('slug', $slug)->first() ?? new Genre;

            $genre->fill(['name' => $name, 'slug' => $slug, 'anilist_name' => $anilistName])->save();
        }
    }
}
