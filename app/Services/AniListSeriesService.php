<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

class AniListSeriesService
{
    private const ENDPOINT = 'https://graphql.anilist.co';

    private const MEDIA_QUERY = <<<'GRAPHQL'
        query ($id: Int) {
          Media(id: $id, type: ANIME) {
            id
            idMal
            format
            status
            episodes
            seasonYear
            startDate { year month day }
            title { romaji english native }
            coverImage { large }
            relations {
              edges {
                relationType
                node { id type }
              }
            }
          }
        }
        GRAPHQL;

    private const SEARCH_QUERY = <<<'GRAPHQL'
        query ($search: String) {
          Page(page: 1, perPage: 8) {
            media(search: $search, type: ANIME, isAdult: false, sort: SEARCH_MATCH) {
              id
              idMal
              format
              status
              episodes
              seasonYear
              startDate { year month day }
              title { romaji english native }
              coverImage { large }
            }
          }
        }
        GRAPHQL;
    
    private const SCHEDULE_QUERY = <<<'GRAPHQL'
        query ($id: Int, $page: Int) {
        Media(id: $id, type: ANIME) {
            streamingEpisodes { title }
            airingSchedule(page: $page, perPage: 50) {
            pageInfo { hasNextPage }
            nodes { episode airingAt }
            }
        }
        }
        GRAPHQL;

    /**
     * Busca candidatos por título (para vincular un anime que aún no tiene anilist_id).
     */
    public function search(string $term): array
    {
        $data = $this->query(self::SEARCH_QUERY, ['search' => $term]);

        return array_map(fn ($media) => $this->normalize($media), $data['Page']['media']);
    }

    public function find(int $id): array
    {
        return $this->normalize($this->query(self::MEDIA_QUERY, ['id' => $id])['Media']);
    }

    /**
     * Respaldo cuando Kitsu no tiene datos: calendario de emisión (número y fecha)
     * más los títulos que AniList conozca por los enlaces de streaming.
     */
    public function airingSchedule(int $id): array
    {
        $episodes = [];
        $streamingTitles = [];
        $page = 1;

        do {
            $media = $this->query(self::SCHEDULE_QUERY, ['id' => $id, 'page' => $page])['Media'];
            $schedule = $media['airingSchedule'];

            if ($page === 1) {
                $streamingTitles = $this->parseStreamingTitles($media['streamingEpisodes'] ?? []);
            }

            foreach ($schedule['nodes'] as $node) {
                $number = (int) $node['episode'];

                $episodes[$number] = [
                    'number' => $number,
                    'title' => $streamingTitles[$number] ?? null,
                    'synopsis' => null,
                    'air_date' => \Carbon\Carbon::createFromTimestamp($node['airingAt'], 'Asia/Tokyo')->toDateString(),
                ];
            }

            $hasNext = (bool) $schedule['pageInfo']['hasNextPage'];
            $page++;

            if ($hasNext) {
                usleep(800_000);
            }
        } while ($hasNext && $page <= 10);

        // Episodios que solo aparecen en los enlaces de streaming
        foreach ($streamingTitles as $number => $title) {
            $episodes[$number] ??= [
                'number' => $number,
                'title' => $title,
                'synopsis' => null,
                'air_date' => null,
            ];
        }

        ksort($episodes);

        return array_values($episodes);
    }

    /**
     * Todas las entradas de la franquicia (precuelas y secuelas), ordenadas por fecha de estreno.
     * Cacheado 6 horas.
     */
    public function franchise(int $rootId): array
    {
        return Cache::remember(
            $this->cacheKey($rootId),
            now()->addHours(6),
            fn () => $this->fetchFranchise($rootId)
        );
    }

    public function forget(int $rootId): void
    {
        Cache::forget($this->cacheKey($rootId));
    }

    private function cacheKey(int $rootId): string
    {
        return "anilist.franchise.{$rootId}";
    }

    private function fetchFranchise(int $rootId): array
    {
        $items = [];
        $queue = [$rootId];

        while ($queue && count($items) < 25) {
            $id = array_shift($queue);

            if (isset($items[$id])) {
                continue;
            }

            $media = $this->query(self::MEDIA_QUERY, ['id' => $id])['Media'];
            $items[$id] = $this->normalize($media);

            foreach ($media['relations']['edges'] ?? [] as $edge) {
                $nodeId = $edge['node']['id'] ?? null;

                if (
                    $nodeId
                    && in_array($edge['relationType'], ['PREQUEL', 'SEQUEL'], true)
                    && ($edge['node']['type'] ?? null) === 'ANIME'
                    && ! isset($items[$nodeId])
                    && ! in_array($nodeId, $queue, true)
                ) {
                    $queue[] = $nodeId;
                }
            }

            if ($queue) {
                usleep(800_000); // respeta el límite de 90 peticiones por minuto
            }
        }

        uasort($items, fn ($a, $b) => $a['sort_date'] <=> $b['sort_date']);

        return array_values($items);
    }

    private function query(string $query, array $variables): array
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(10)
                ->timeout(20)
                ->retry(3, 2000, throw: false)
                ->post(self::ENDPOINT, ['query' => $query, 'variables' => $variables]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('No se pudo conectar con AniList.');
        }

        if ($response->failed() || $response->json('errors')) {
            throw new RuntimeException($response->json('errors.0.message') ?? 'HTTP ' . $response->status());
        }

        return $response->json('data');
    }

    /**
     * "Episode 3 - Title" / "E3 - Title" → [3 => 'Title']
     */
    private function parseStreamingTitles(array $streamingEpisodes): array
    {
        $titles = [];

        foreach ($streamingEpisodes as $item) {
            if (preg_match('/^(?:episode|ep\.?|e)\s*(\d+)\s*[-–—:.]\s*(.+)$/i', trim($item['title'] ?? ''), $m)) {
                $titles[(int) $m[1]] ??= trim($m[2]);
            }
        }

        return $titles;
    }

    private function normalize(array $media): array
    {
        $title = $media['title'];
        $date = $media['startDate'] ?? [];

        return [
            'anilist_id' => $media['id'],
            'mal_id' => $media['idMal'],
            'title' => $title['english'] ?: ($title['romaji'] ?: $title['native']),
            'romaji' => $title['romaji'],
            'format' => $media['format'],
            'status' => $media['status'],
            'episodes' => $media['episodes'],
            'year' => $date['year'] ?? $media['seasonYear'],
            'thumb' => $media['coverImage']['large'] ?? null,
            'sort_date' => sprintf('%04d%02d%02d', $date['year'] ?? 9999, $date['month'] ?? 12, $date['day'] ?? 31),
        ];
    }
}