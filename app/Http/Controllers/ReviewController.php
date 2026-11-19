<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Calificar (1 a 5 estrellas) y comentar un anime o un episodio.
 */
class ReviewController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:anime,episodio'],
            'id' => ['required', 'integer'],
            'stars' => ['required', 'integer', 'between:1,5'],
            'body' => ['nullable', 'string', 'max:1000'],
        ], [
            'stars.required' => 'Elige de 1 a 5 estrellas.',
            'stars.between' => 'La calificación va de 1 a 5 estrellas.',
            'body.max' => 'El comentario no puede pasar de 1000 caracteres.',
        ]);

        $item = $data['type'] === 'anime'
            ? Anime::findOrFail($data['id'])
            : Episode::findOrFail($data['id']);

        $userId = $request->user()->id;
        $body = trim((string) ($data['body'] ?? ''));

        DB::transaction(function () use ($item, $userId, $data, $body) {
            // Una calificación por usuario: si ya existe, se actualiza
            $item->ratings()->updateOrCreate(['user_id' => $userId], ['stars' => $data['stars']]);

            if ($body !== '') {
                $item->comments()->create(['user_id' => $userId, 'body' => $body]);
            }
        });

        return back()->withFragment('calificaciones')->with(
            'status',
            $body !== '' ? 'Gracias por tu calificación y tu comentario.' : 'Gracias por tu calificación.'
        );
    }
}
