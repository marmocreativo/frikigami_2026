<?php

namespace App\Services;

use App\Contracts\TextGenerator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiTextGenerator implements TextGenerator
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function generate(string $system, string $prompt, array $options = []): ?string
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            return null;
        }

        $generationConfig = [
            'temperature' => $options['temperature'] ?? 0.5,
            'maxOutputTokens' => $options['max_tokens'] ?? 4096,
        ];

        if (! empty($options['json'])) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $key])
                ->connectTimeout(10)
                ->timeout(120)
                ->post(sprintf(self::ENDPOINT, $options['model'] ?? config('services.gemini.model')), [
                    'systemInstruction' => ['parts' => [['text' => $system]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'generationConfig' => $generationConfig,
                ]);

            if ($response->failed()) {
                Log::warning('Gemini respondió con error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = collect($response->json('candidates.0.content.parts', []))
                ->pluck('text')
                ->implode('');

            return $text !== '' ? $text : null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}