<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Services\NewsExpander;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNewsAiController extends Controller
{
    public function expand(Request $request, NewsExpander $expander): JsonResponse
    {
        set_time_limit(180);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:20', 'max:8000'],
            'keyword' => ['nullable', 'string', 'max:100'],
            'length' => ['required', 'in:short,medium,long'],
            'anime_ids' => ['nullable', 'array', 'max:10'],
            'anime_ids.*' => ['integer', 'exists:animes,id'],
        ], [
            'body.required' => 'Escribe primero el contenido de la nota.',
            'body.min' => 'El contenido es muy corto: escribe al menos unas líneas para poder expandirlo.',
        ]);

        $animeTitles = Anime::whereIn('id', $data['anime_ids'] ?? [])->pluck('title')->all();

        $result = $expander->expand(
            $data['title'] ?? '',
            $data['body'],
            $animeTitles,
            $data['keyword'] ?? null,
            $data['length'],
        );

        if (! $result) {
            return response()->json([
                'message' => 'No se pudo generar el contenido. Revisa la llave y el modelo del proveedor de IA (storage/logs/laravel.log) o inténtalo de nuevo.',
            ], 502);
        }

        return response()->json(['data' => $result]);
    }
}