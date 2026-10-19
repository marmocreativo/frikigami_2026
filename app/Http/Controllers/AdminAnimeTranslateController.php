<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Services\AnimeContentTranslator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminAnimeTranslateController extends Controller
{
    public function store(Request $request, Anime $anime, AnimeContentTranslator $translator): RedirectResponse
    {
        set_time_limit(300);

        $data = $request->validate([
            'types' => ['required', 'array'],
            'types.*' => ['in:' . implode(',', array_keys(AnimeContentTranslator::TYPES))],
        ], [
            'types.required' => 'Elige al menos un tipo de contenido para traducir.',
        ]);

        $result = $translator->translate($anime, $data['types']);

        if ($result['translated'] === 0 && $result['failed'] === 0 && $result['left'] === 0) {
            $message = 'No había contenido en inglés por traducir.';
        } else {
            $parts = ["{$result['translated']} textos traducidos"];

            if ($result['failed']) {
                $parts[] = "{$result['failed']} no se pudieron traducir (el traductor no respondió)";
            }

            if ($result['left']) {
                $parts[] = "quedan {$result['left']} por traducir: vuelve a pulsar el botón";
            }

            $message = ucfirst(implode('; ', $parts)) . '.';
        }

        return redirect()->route('admin.animes.edit', $anime)->with('status', $message);
    }
}