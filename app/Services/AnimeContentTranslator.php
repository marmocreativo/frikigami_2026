<?php

namespace App\Services;

use App\Contracts\Translator;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AnimeContentTranslator
{
    public const TYPES = [
        'anime' => 'Sinopsis del anime',
        'episodes' => 'Sinopsis de los episodios',
        'characters' => 'Descripción de los personajes',
        'people' => 'Biografía del cast y staff',
    ];

    private const TARGETS = [
        'anime' => [Anime::class, 'synopsis'],
        'episodes' => [Episode::class, 'synopsis'],
        'characters' => [Character::class, 'description'],
        'people' => [Person::class, 'bio'],
    ];

    public function __construct(private Translator $translator)
    {
    }

    /**
     * Textos en inglés pendientes, por tipo: ['episodes' => [id => texto, ...], ...]
     *
     * @return array<string, array<int, string>>
     */
    public function pending(Anime $anime, ?array $types = null): array
    {
        $pending = [];

        foreach ($types ?? array_keys(self::TYPES) as $type) {
            $pending[$type] = $this->englishTexts($this->query($anime, $type), self::TARGETS[$type][1]);
        }

        return $pending;
    }

    /**
     * Traduce hasta $limit textos y los guarda.
     *
     * @return array{translated: int, failed: int, left: int}
     */
    public function translate(Anime $anime, array $types, int $limit = 60): array
    {
        $remaining = $limit;
        $translatedCount = 0;
        $failed = 0;
        $left = 0;

        foreach ($this->pending($anime, $types) as $type => $texts) {
            $batch = array_slice($texts, 0, max($remaining, 0), true);
            $left += count($texts) - count($batch);

            if ($batch === []) {
                continue;
            }

            [$model, $column] = self::TARGETS[$type];

            $translated = $this->translator->toSpanish($batch) ?? [];

            foreach ($translated as $id => $text) {
                $model::whereKey($id)->update([$column => $text]);
            }

            $translatedCount += count($translated);
            $failed += count($batch) - count($translated);
            $remaining -= count($batch);
        }

        return ['translated' => $translatedCount, 'failed' => $failed, 'left' => $left];
    }

    private function query(Anime $anime, string $type): Builder
    {
        return match ($type) {
            'anime' => Anime::whereKey($anime->id),
            'episodes' => Episode::whereIn('anime_season_id', $anime->animeSeasons()->pluck('id')),
            'characters' => Character::whereIn('id', $anime->characters()->pluck('characters.id')),
            'people' => Person::whereIn('id', $this->personIds($anime)),
        };
    }

    /**
     * Staff (anime_person) y actores de voz (character_person) de este anime.
     */
    private function personIds(Anime $anime): array
    {
        $staff = DB::table('anime_person')->where('anime_id', $anime->id)->pluck('person_id');
        $voices = DB::table('character_person')->where('anime_id', $anime->id)->pluck('person_id');

        return $staff->merge($voices)->unique()->values()->all();
    }

    private function englishTexts(Builder $query, string $column): array
    {
        return $query
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->get(['id', $column])
            ->filter(fn ($row) => $this->looksEnglish($row->{$column}))
            ->mapWithKeys(fn ($row) => [$row->id => $row->{$column}])
            ->all();
    }

    /**
     * Heurística simple: más palabras comunes del inglés que del español.
     */
    private function looksEnglish(string $text): bool
    {
        $english = preg_match_all('/\b(the|and|of|to|in|is|with|his|her|that|for)\b/i', $text);
        $spanish = preg_match_all('/\b(el|la|los|las|de|que|y|en|con|su|para|una|un)\b/i', $text);

        return $english > $spanish;
    }
}