<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Descarga las imágenes previas de episodios al disco público.
 */
class EpisodeThumbnails
{
    private const MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @param  array<int, string>  $urls  número de episodio => URL de la imagen
     * @param  string  $directory  carpeta dentro del disco público, ej. "episodes/frieren/t2"
     * @return array<int, string> número de episodio => ruta guardada (solo los que se descargaron)
     */
    public function download(array $urls, string $directory): array
    {
        $saved = [];

        foreach (array_chunk($urls, 8, preserve_keys: true) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk) {
                foreach ($chunk as $number => $url) {
                    $pool->as((string) $number)->timeout(15)->get($url);
                }
            });

            foreach ($chunk as $number => $url) {
                $response = $responses[(string) $number] ?? null;

                if (! $response instanceof Response || $response->failed()) {
                    continue;
                }

                $extension = self::MIMES[Str::before((string) $response->header('Content-Type'), ';')] ?? null;

                if (! $extension || $response->body() === '') {
                    continue;
                }

                $path = trim($directory, '/') . '/' . sprintf('%03d', $number) . '.' . $extension;
                Storage::disk('public')->put($path, $response->body());
                $saved[$number] = $path;
            }
        }

        return $saved;
    }
}
