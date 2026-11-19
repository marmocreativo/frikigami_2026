<?php

namespace App\Services;

use App\Models\Season;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AniListService
{
    private const ENDPOINT = 'https://graphql.anilist.co';

    private const SEASONS = [
        'Invierno' => 'WINTER',
        'Primavera' => 'SPRING',
        'Verano' => 'SUMMER',
        'Otoño' => 'FALL',
    ];

    private const QUERY = <<<'GRAPHQL'
        query ($page: Int, $season: MediaSeason, $seasonYear: Int) {
          Page(page: $page, perPage: 50) {
            pageInfo { hasNextPage }
            media(season: $season, seasonYear: $seasonYear, type: ANIME, isAdult: false, sort: POPULARITY_DESC) {
              id
              idMal
              format
              status
              genres
              episodes
              title { romaji english native }
              synonyms
              description(asHtml: false)
              coverImage { extraLarge large }
              relations {
                edges {
                  relationType
                  node { id type }
                }
              }
            }
          }
        }
        GRAPHQL;

    /**
     * Todos los animes de una temporada (cacheados 6 horas).
     */
    public function season(Season $season): array
    {
        return Cache::remember(
            $this->cacheKey($season),
            now()->addHours(6),
            fn () => $this->fetchSeason($this->enumFor($season), $season->year)
        );
    }

    private const MEDIA_QUERY = <<<'GRAPHQL'
        query ($id: Int) {
          Media(id: $id, type: ANIME) {
            id
            idMal
            format
            status
            genres
            episodes
            season
            seasonYear
            startDate { year month }
            title { romaji english native }
            synonyms
            description(asHtml: false)
            coverImage { extraLarge large }
            relations {
              edges {
                relationType
                node { id type }
              }
            }
          }
        }
        GRAPHQL;

    /**
     * Datos completos de una entrada (sinopsis, portada, estreno...).
     */
    public function media(int $id): array
    {
        $response = Http::acceptJson()
            ->timeout(20)
            ->retry(3, 2000, throw: false)
            ->post(self::ENDPOINT, ['query' => self::MEDIA_QUERY, 'variables' => ['id' => $id]]);

        if ($response->failed() || $response->json('errors')) {
            throw new RuntimeException($response->json('errors.0.message') ?? 'HTTP ' . $response->status());
        }

        return $this->normalizeItem($response->json('data.Media'));
    }

    public function forget(Season $season): void
    {
        Cache::forget($this->cacheKey($season));
    }

    public static function normalize(?string $title): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii((string) $title))));
    }

    public static function mapStatus(?string $status): string
    {
        return match ($status) {
            'RELEASING', 'HIATUS' => 'ongoing',
            'FINISHED', 'CANCELLED' => 'finished',
            default => 'upcoming',
        };
    }

    /**
     * Descarga la portada y la guarda en storage/app/public/animes.
     */
    public function downloadCover(?string $url, string $slug): ?string
    {
        if (! $url) {
            return null;
        }

        try {
            $response = Http::timeout(20)->get($url);

            if ($response->failed()) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
            $path = "animes/{$slug}.{$extension}";

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    private function enumFor(Season $season): string
    {
        return self::SEASONS[$season->name] ?? throw new RuntimeException('Temporada no soportada.');
    }

    private function cacheKey(Season $season): string
    {
        // v3: los items incluyen prequel_id y genres
        return "anilist.season.v3.{$season->year}." . $this->enumFor($season);
    }

    private function fetchSeason(string $season, int $year): array
    {
        $items = [];
        $page = 1;

        do {
            $response = Http::acceptJson()
                ->timeout(20)
                ->retry(3, 2000, throw: false)
                ->post(self::ENDPOINT, [
                    'query' => self::QUERY,
                    'variables' => [
                        'page' => $page,
                        'season' => $season,
                        'seasonYear' => $year,
                    ],
                ]);

            if ($response->failed() || $response->json('errors')) {
                throw new RuntimeException($response->json('errors.0.message') ?? 'HTTP ' . $response->status());
            }

            $data = $response->json('data.Page');

            foreach ($data['media'] as $media) {
                $items[] = $this->normalizeItem($media);
            }

            $hasNext = (bool) $data['pageInfo']['hasNextPage'];
            $page++;

            if ($hasNext) {
                usleep(800_000); // respeta el límite de 90 peticiones por minuto
            }
        } while ($hasNext && $page <= 10);

        return $items;
    }

    private function normalizeItem(array $media): array
    {
        $title = $media['title'];

        return [
            'anilist_id' => $media['id'],
            'mal_id' => $media['idMal'],
            'title' => $title['english'] ?: ($title['romaji'] ?: $title['native']),
            'romaji' => $title['romaji'],
            // Títulos usados para emparejar (sin el japonés)
            'titles' => array_values(array_unique(array_filter([
                $title['romaji'],
                $title['english'],
                ...($media['synonyms'] ?? []),
            ]))),
            'format' => $media['format'],
            'status' => $media['status'],
            'episodes' => $media['episodes'],
            'synopsis' => $this->cleanDescription($media['description']),
            'cover' => $media['coverImage']['extraLarge'] ?? $media['coverImage']['large'] ?? null,
            'thumb' => $media['coverImage']['large'] ?? null,
            // Si es continuación de otro anime, el id de su precuela en AniList
            'prequel_id' => $this->prequelId($media),
            'estreno' => $this->estrenoOf($media),
            // Nombres de género de AniList (en inglés); se emparejan con genres.anilist_name
            'genres' => $media['genres'] ?? [],
        ];
    }

    /** @return array{name: string, year: int}|null */
    private function estrenoOf(array $media): ?array
    {
        $year = $media['seasonYear'] ?? ($media['startDate']['year'] ?? null);
        $name = array_search($media['season'] ?? null, self::SEASONS, true);

        if (! $name) {
            $month = $media['startDate']['month'] ?? null;
            $name = $month ? match (true) {
                $month <= 2 || $month === 12 => 'Invierno',
                $month <= 5 => 'Primavera',
                $month <= 8 => 'Verano',
                default => 'Otoño',
            } : null;
        }

        return ($name && $year) ? ['name' => $name, 'year' => (int) $year] : null;
    }

    private function prequelId(array $media): ?int
    {
        foreach ($media['relations']['edges'] ?? [] as $edge) {
            if (($edge['relationType'] ?? null) === 'PREQUEL' && ($edge['node']['type'] ?? null) === 'ANIME') {
                return (int) $edge['node']['id'];
            }
        }

        return null;
    }

    private function cleanDescription(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/~!.*?!~/s', '', $text);                 // spoilers de AniList
        $text = strip_tags($text);
        $text = preg_replace('/\((Source|Fuente):[^)]*\)/i', '', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace("/\n{3,}/", "\n\n", $text));

        return $text !== '' ? $text : null;
    }
}