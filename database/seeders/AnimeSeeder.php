<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\AnimeSeason;
use App\Models\Character;
use App\Models\Genre;
use App\Models\Person;
use App\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnimeSeeder extends Seeder
{
    public function run(): void
    {
        $animes = [
            [
                'title'    => 'Jujutsu Kaisen',
                'synopsis' => 'Yuji Itadori es un estudiante de preparatoria que se une a una organización secreta de hechiceros para eliminar a un poderoso maldito llamado Ryomen Sukuna.',
                'status'   => 'ongoing',
                'season'   => ['name' => 'Otoño', 'year' => 2025],
                'genres'   => ['Acción', 'Sobrenatural', 'Shounen'],
                'characters' => ['Yuji Itadori', 'Megumi Fushiguro', 'Nobara Kugisaki', 'Satoru Gojo'],
                'staff'    => [['name' => 'Gege Akutami', 'role' => 'Mangaka']],
                'seasons'  => [
                    ['number' => 1, 'title' => 'Temporada 1', 'year' => 2020, 'episodes' => 24],
                    ['number' => 2, 'title' => 'Temporada 2', 'year' => 2023, 'episodes' => 23],
                ],
            ],
            [
                'title'    => 'Shingeki no Kyojin',
                'synopsis' => 'En un mundo donde la humanidad vive dentro de ciudades rodeadas de enormes muros, los titanes amenazan con devorar a todos los seres humanos.',
                'status'   => 'finished',
                'season'   => ['name' => 'Invierno', 'year' => 2025],
                'genres'   => ['Acción', 'Drama', 'Fantasía'],
                'characters' => ['Eren Yeager', 'Mikasa Ackerman', 'Armin Arlert'],
                'staff'    => [['name' => 'Hajime Isayama', 'role' => 'Mangaka']],
                'seasons'  => [
                    ['number' => 1, 'title' => 'Temporada 1', 'year' => 2013, 'episodes' => 25],
                    ['number' => 2, 'title' => 'Temporada 2', 'year' => 2017, 'episodes' => 12],
                    ['number' => 3, 'title' => 'Temporada 3', 'year' => 2018, 'episodes' => 22],
                    ['number' => 4, 'title' => 'The Final Season', 'year' => 2020, 'episodes' => 28],
                ],
            ],
            [
                'title'    => 'Demon Slayer',
                'synopsis' => 'Tanjiro Kamado se convierte en cazador de demonios tras el ataque que destruyó a su familia y convirtió a su hermana en un demonio.',
                'status'   => 'ongoing',
                'season'   => ['name' => 'Primavera', 'year' => 2026],
                'genres'   => ['Acción', 'Aventura', 'Sobrenatural'],
                'characters' => [],
                'staff'    => [['name' => 'Hiroshi Seko', 'role' => 'Guionista']],
                'seasons'  => [
                    ['number' => 1, 'title' => 'Temporada 1', 'year' => 2019, 'episodes' => 26],
                    ['number' => 2, 'title' => 'Temporada 2', 'year' => 2021, 'episodes' => 18],
                ],
            ],
        ];

        foreach ($animes as $data) {
            $season = Season::where('name', $data['season']['name'])
                ->where('year', $data['season']['year'])
                ->first();

            $anime = Anime::create([
                'title'     => $data['title'],
                'slug'      => Str::slug($data['title']),
                'synopsis'  => $data['synopsis'],
                'status'    => $data['status'],
                'season_id' => $season?->id,
            ]);

            // Géneros
            $genreIds = Genre::whereIn('name', $data['genres'])->pluck('id');
            $anime->genres()->sync($genreIds);

            // Personajes
            $characterIds = Character::whereIn('name', $data['characters'])->pluck('id');
            $anime->characters()->sync($characterIds);

            // Staff
            foreach ($data['staff'] as $staffData) {
                $person = Person::where('name', $staffData['name'])->first();
                if ($person) {
                    $anime->staff()->attach($person->id, ['role' => $staffData['role']]);
                }
            }

            // Temporadas de la serie y episodios
            foreach ($data['seasons'] as $seasonData) {
                $animeSeason = AnimeSeason::create([
                    'anime_id' => $anime->id,
                    'number'   => $seasonData['number'],
                    'title'    => $seasonData['title'],
                    'year'     => $seasonData['year'],
                ]);

                for ($i = 1; $i <= $seasonData['episodes']; $i++) {
                    $animeSeason->episodes()->create([
                        'user_id' => 1,
                        'number'  => $i,
                        'title'   => 'Episodio ' . $i,
                    ]);
                }
            }
        }
    }
}