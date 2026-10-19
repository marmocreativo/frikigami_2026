<?php

namespace App\Services;

use App\Contracts\TextGenerator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnthropicTextGenerator implements TextGenerator
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    public function generate(string $system, string $prompt, array $options = []): ?string
    {
        $key = config('services.anthropic.key');

        if (blank($key)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                ])
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(120)
                ->post(self::ENDPOINT, [
                    'model' => $options['model']
                        ?? (config('services.anthropic.writing_model') ?: config('services.anthropic.model')),
                    'max_tokens' => $options['max_tokens'] ?? 4096,
                    'temperature' => $options['temperature'] ?? 0.5,
                    'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);

            if ($response->failed()) {
                Log::warning('Anthropic respondió con error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = collect($response->json('content', []))
                ->where('type', 'text')
                ->pluck('text')
                ->implode('');

            return $text !== '' ? $text : null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}