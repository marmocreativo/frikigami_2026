<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Contracts\Translator;
use Throwable;

class GeminiTranslator implements Translator
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    private const SYSTEM_PROMPT = <<<'TXT'
        Eres un traductor profesional de sinopsis de anime. Traduce cada texto del inglés al español latinoamericano neutro.
        Reglas:
        - Conserva tal cual los nombres de personajes, lugares, organizaciones y títulos de obras: no los traduzcas ni los adaptes.
        - Respeta los saltos de línea y los párrafos del original.
        - No agregues ni quites información, y no incluyas comentarios, notas ni el texto original.
        Recibirás un arreglo JSON de objetos {"id", "text"}. Devuelve un arreglo JSON con el mismo "id" y la traducción en "text".
        TXT;

    /**
     * Traduce de inglés a español varios textos en una sola petición.
     * Devuelve [clave => traducción] solo de los que se tradujeron, o null si falla todo.
     *
     * @param  array<int|string, string>  $texts
     */
    public function toSpanish(array $texts): ?array
    {
        $key = config('services.gemini.key');

        if (blank($key) || $texts === []) {
            return null;
        }

        $payload = [];
        foreach ($texts as $id => $text) {
            $payload[] = ['id' => (string) $id, 'text' => $text];
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $key])
                ->timeout(90)
                ->post(sprintf(self::ENDPOINT, config('services.gemini.model')), [
                    'systemInstruction' => [
                        'parts' => [['text' => self::SYSTEM_PROMPT]],
                    ],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [['text' => json_encode($payload, JSON_UNESCAPED_UNICODE)]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'ARRAY',
                            'items' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'id' => ['type' => 'STRING'],
                                    'text' => ['type' => 'STRING'],
                                ],
                                'required' => ['id', 'text'],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Gemini respondió con error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $raw = collect($response->json('candidates.0.content.parts', []))
                ->pluck('text')
                ->implode('');

            // Por si el modelo envuelve el JSON en bloques de código
            $raw = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($raw)));
            $items = json_decode($raw, true);

            if (! is_array($items)) {
                Log::warning('Gemini no devolvió JSON válido', [
                    'finishReason' => $response->json('candidates.0.finishReason'),
                    'blockReason' => $response->json('promptFeedback.blockReason'),
                ]);

                return null;
            }

            $result = [];

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                $translated = trim((string) ($item['text'] ?? ''));

                if ($id !== null && $translated !== '' && array_key_exists($id, $texts)) {
                    $result[$id] = $translated;
                }
            }

            return $result ?: null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}