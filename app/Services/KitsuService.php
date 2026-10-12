<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class KitsuService
{
    private const BASE = 'https://kitsu.io/api/edge';

    /**
     * Episodios de un anime: [['number', 'title', 'synopsis', 'air_date'], ...].
     * Busca la entrada de Kitsu a partir del id de MyAnimeList o de AniList.
     * Devuelve [] si Kitsu no conoce el anime.
     */
    public function episodes(?int $malId, ?int $anilistId): array
    {
        $kitsuId = $this->resolveKitsuId($malId, $anilistId);

        if (! $kitsuId) {
            return [];
        }

        $key = "kitsu.episodes.{$kitsuId}";

        if (($cached = Cache::get($key)) !== null) {
            return $cached;
        }

        $episodes = $this->fetchEpisodes($kitsuId);

        // No se cachea una lista vacía (puede que aún no estén cargados los episodios)
        if ($episodes !== []) {
            Cache::put($key, $episodes, now()->addHours(6));
        }

        return $episodes;
    }

    public function forget(?int $malId, ?int $anilistId): void
    {
        foreach ($this->externalIds($malId, $anilistId) as $site => $externalId) {
            $kitsuId = Cache::get($this->idCacheKey($site, $externalId));

            if ($kitsuId) {
                Cache::forget("kitsu.episodes.{$kitsuId}");
            }
        }
    }

    private function externalIds(?int $malId, ?int $anilistId): array
    {
        return array_filter([
            'myanimelist/anime' => $malId,
            'anilist/anime' => $anilistId,
        ]);
    }

    private function idCacheKey(string $site, int $externalId): string
    {
        return 'kitsu.id.' . str_replace('/', '-', $site) . ".{$externalId}";
    }

    private function resolveKitsuId(?int $malId, ?int $anilistId): ?int
    {
        foreach ($this->externalIds($malId, $anilistId) as $site => $externalId) {
            $key = $this->idCacheKey($site, $externalId);

            if ($cached = Cache::get($key)) {
                return (int) $cached;
            }

            $response = $this->get('/mappings', [
                'filter[externalSite]' => $site,
                'filter[externalId]' => $externalId,
                'include' => 'item',
                'page[limit]' => 1,
            ]);

            if (! $response->successful()) {
                continue;
            }

            $id = $response->json('data.0.relationships.item.data.id')
                ?? $response->json('included.0.id');

            if ($id) {
                Cache::put($key, (int) $id, now()->addDays(30));

                return (int) $id;
            }
        }

        return null;
    }

    private function fetchEpisodes(int $kitsuId): array
    {
        $episodes = [];
        $offset = 0;

        do {
            $response = $this->get("/anime/{$kitsuId}/episodes", [
                'page[limit]' => 20,
                'page[offset]' => $offset,
                'sort' => 'number',
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('Kitsu respondió HTTP ' . $response->status());
            }

            foreach ($response->json('data', []) as $episode) {
                $attributes = $episode['attributes'];
                $number = (int) ($attributes['number'] ?? 0);

                if ($number < 1) {
                    continue;
                }

                $title = $attributes['titles']['en_us']
                    ?? $attributes['titles']['en_jp']
                    ?? $attributes['canonicalTitle']
                    ?? null;

                // Descarta títulos genéricos como "Episode 5"
                if ($title && preg_match('/^(episode|ep\.?)\s*\d+$/i', trim($title))) {
                    $title = null;
                }

                $episodes[] = [
                    'number' => $number,
                    'title' => $title ?: null,
                    'synopsis' => ($attributes['synopsis'] ?? null) ?: ($attributes['description'] ?? null),
                    'air_date' => ! empty($attributes['airdate']) ? substr($attributes['airdate'], 0, 10) : null,
                ];
            }

            $hasNext = ! empty($response->json('links.next'));
            $offset += 20;

            if ($hasNext) {
                usleep(300_000);
            }
        } while ($hasNext && $offset < 2000);

        return $episodes;
    }

    private function get(string $path, array $query): Response
    {
        try {
            return Http::withHeaders(['Accept' => 'application/vnd.api+json'])
                ->connectTimeout(10)
                ->timeout(30)
                ->retry(2, 1500, throw: false)
                ->get(self::BASE . $path, $query);
        } catch (ConnectionException $e) {
            throw new RuntimeException('No se pudo conectar con Kitsu.');
        }
    }
}