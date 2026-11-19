<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Arranque en limpio: usuario admin, estrenos y géneros. Sin animes.
     * (PersonSeeder, CharacterSeeder, AnimeSeeder y NewsSeeder
     * siguen en el proyecto, pero ya no se llaman desde aquí.)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SeasonSeeder::class,
            GenreSeeder::class,
        ]);
    }
}
