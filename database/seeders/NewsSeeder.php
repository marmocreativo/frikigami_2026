<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $jjk = Anime::where('slug', 'jujutsu-kaisen')->first();
        $snk = Anime::where('slug', 'shingeki-no-kyojin')->first();

        $newsItems = [
            [
                'title' => 'Jujutsu Kaisen anuncia nueva temporada para 2026',
                'body'  => 'El estudio MAPPA ha confirmado oficialmente que Jujutsu Kaisen tendrá una nueva entrega en 2026. Los fans esperan con ansias el regreso de Yuji Itadori y sus compañeros en nuevas y emocionantes batallas.',
                'anime' => $jjk,
            ],
            [
                'title' => 'Shingeki no Kyojin: El legado de la serie más épica del anime',
                'body'  => 'A años de su conclusión, Shingeki no Kyojin sigue siendo uno de los animes más comentados y analizados de la comunidad. Repasamos su impacto cultural y narrativo.',
                'anime' => $snk,
            ],
            [
                'title' => 'Los mejores animes del año según la comunidad',
                'body'  => 'La comunidad de Frikigami ha votado y estos son los animes más destacados del año. Desde shonens épicos hasta slice of life emotivos, hay para todos los gustos.',
                'anime' => null,
            ],
        ];

        foreach ($newsItems as $item) {
            $news = News::create([
                'user_id'      => 1,
                'title'        => $item['title'],
                'slug'         => Str::slug($item['title']),
                'body'         => $item['body'],
                'published_at' => now(),
            ]);

            if ($item['anime']) {
                $news->animes()->attach($item['anime']->id);
            }
        }
    }
}