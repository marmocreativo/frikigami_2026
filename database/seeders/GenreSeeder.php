<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'Acción', 'Aventura', 'Comedia', 'Drama', 'Fantasía',
            'Romance', 'Ciencia Ficción', 'Terror', 'Misterio',
            'Slice of Life', 'Deportes', 'Sobrenatural', 'Mechas',
            'Isekai', 'Shounen', 'Shoujo', 'Seinen', 'Josei',
        ];

        foreach ($genres as $name) {
            Genre::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}