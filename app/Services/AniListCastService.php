<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AniListCastService
{
    private const ENDPOINT = 'https://graphql.anilist.co';

    private const CHARACTERS_QUERY = <<<'GRAPHQL'
        query ($id: Int, $page: Int) {
          Media(id: $id, type: ANIME) {
            characters(page: $page, perPage: 25, sort: [ROLE, RELEVANCE]) {
              pageInfo { hasNextPage }
              edges {
                role
                node {
                  id
                  name { full native }
                  image { large }
                  description(asHtml: false)
                }
                voiceActors(language: JAPANESE) {
                  id
                  name { full native }
                  image { large }
                }
              }
            }
          }
        }
        GRAPHQL;

    private const STAFF_QUERY = <<<'GRAPHQL'
        query ($id: Int, $page: Int) {
          Media(id: $id, type: ANIME) {
            staff(page: $page, perPage: 25) {
              pageInfo { hasNextPage }
              edges {
                role
                node {
                  id
                  name { full native }
                  image { large }
                  description(asHtml: false)
                }
              }
            }
          }
        }
        GRAPHQL;

    /**
     * Personajes (con actores de voz) y staff de un anime. Cacheado 6 horas.
     *
     * @return array{characters: array, staff: array}
     */
    public function cast(int $mediaId): array
    {
        return Cache::remember(
            $this->cacheKey($mediaId),
            now()->addHours(6),
            fn () => [
                'characters' => $this->fetchCharacters($mediaId),
                'staff' => $this->fetchStaff($mediaId),
            ]
        );
    }

    public function forget(int $mediaId): void
    {
        Cache::forget($this->cacheKey($mediaId));
    }

    /**
     * Descarga una imagen a storage/app/public/{carpeta}. Devuelve la ruta o null.
     */
    public function downloadImage(?string $url, string $folder, string $slug): ?string
    {
        // AniList usa una imagen genérica "default" cuando no hay foto
        if (! $url || str_contains($url, '/default.')) {
            return null;
        }

        try {
            $response = Http::timeout(20)->get($url);

            if ($response->failed()) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
            $path = "{$folder}/{$slug}.{$extension}";

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    private function cacheKey(int $mediaId): string
    {
        return "anilist.cast.{$mediaId}";
    }

    private function fetchCharacters(int $mediaId): array
    {
        $items = [];
        $page = 1;

        do {
            $connection = $this->query(self::CHARACTERS_QUERY, ['id' => $mediaId, 'page' => $page])['Media']['characters'];

            foreach ($connection['edges'] as $edge) {
                if ($edge['role'] === 'BACKGROUND') {
                    continue;
                }

                $node = $edge['node'];

                $items[] = [
                    'anilist_id' => $node['id'],
                    'name' => $this->name($node['name']),
                    'role' => $edge['role'],
                    'description' => $this->cleanDescription($node['description']),
                    'image' => $node['image']['large'] ?? null,
                    'voice_actors' => array_map(fn ($actor) => [
                        'anilist_id' => $actor['id'],
                        'name' => $this->name($actor['name']),
                        'description' => null,
                        'image' => $actor['image']['large'] ?? null,
                    ], $edge['voiceActors'] ?? []),
                ];
            }

            $hasNext = (bool) $connection['pageInfo']['hasNextPage'];
            $page++;

            if ($hasNext) {
                usleep(800_000);
            }
        } while ($hasNext && $page <= 2); // hasta 50 personajes

        return $items;
    }

    private function fetchStaff(int $mediaId): array
    {
        $items = [];
        $page = 1;

        do {
            $connection = $this->query(self::STAFF_QUERY, ['id' => $mediaId, 'page' => $page])['Media']['staff'];

            foreach ($connection['edges'] as $edge) {
                $node = $edge['node'];

                $items[] = [
                    'anilist_id' => $node['id'],
                    'name' => $this->name($node['name']),
                    'role' => $edge['role'],
                    'description' => $this->cleanDescription($node['description']),
                    'image' => $node['image']['large'] ?? null,
                ];
            }

            $hasNext = (bool) $connection['pageInfo']['hasNextPage'];
            $page++;

            if ($hasNext) {
                usleep(800_000);
            }
        } while ($hasNext && $page <= 2); // hasta 50 integrantes del staff

        return $items;
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

    private function name(array $name): string
    {
        return $name['full'] ?: ($name['native'] ?: 'Sin nombre');
    }

    private function cleanDescription(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/~!.*?!~/s', '', $text);   // spoilers de AniList
        $text = strip_tags($text);
        $text = str_replace(['__', '**'], '', $text);    // negritas en markdown
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace("/\n{3,}/", "\n\n", $text));

        return $text !== '' ? $text : null;
    }
}