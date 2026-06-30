<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeSeason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnimeSeasonController extends Controller
{
    public function index(): View
    {
        $animeSeasons = AnimeSeason::with('anime')
            ->orderBy('anime_id')
            ->orderBy('number')
            ->paginate(20);

        return view('admin.anime_seasons.index', compact('animeSeasons'));
    }

    public function create(): View
    {
        $animes = Anime::orderBy('title')->get();

        return view('admin.anime_seasons.create', compact('animes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'anime_id' => ['required', 'exists:animes,id'],
            'number'   => ['required', 'integer', 'min:1'],
            'title'    => ['nullable', 'string', 'max:255'],
            'year'     => ['nullable', 'integer', 'min:1900', 'max:2100'],
        ]);

        AnimeSeason::create($data);

        return redirect()->route('admin.anime-seasons.index')
            ->with('status', 'Temporada creada correctamente.');
    }

    public function show(AnimeSeason $animeSeason): RedirectResponse
    {
        return redirect()->route('animes.show', [
            'anime' => $animeSeason->anime,
            'tab'   => 'episodes',
        ]);
    }

    public function edit(AnimeSeason $animeSeason): View
    {
        $animes = Anime::orderBy('title')->get();

        return view('admin.anime_seasons.edit', compact('animeSeason', 'animes'));
    }

    public function update(Request $request, AnimeSeason $animeSeason): RedirectResponse
    {
        $data = $request->validate([
            'anime_id' => ['required', 'exists:animes,id'],
            'number'   => ['required', 'integer', 'min:1'],
            'title'    => ['nullable', 'string', 'max:255'],
            'year'     => ['nullable', 'integer', 'min:1900', 'max:2100'],
        ]);

        $animeSeason->update($data);

        return redirect()->route('admin.anime-seasons.index')
            ->with('status', 'Temporada actualizada correctamente.');
    }

    public function destroy(AnimeSeason $animeSeason): RedirectResponse
    {
        $animeSeason->delete();

        return redirect()->route('admin.anime-seasons.index')
            ->with('status', 'Temporada eliminada.');
    }
}