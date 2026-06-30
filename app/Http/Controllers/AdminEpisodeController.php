<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeSeason;
use App\Models\Episode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEpisodeController extends Controller
{
    public function index(): View
    {
        $episodes = Episode::with('animeSeason.anime')
            ->latest()
            ->paginate(20);

        return view('admin.episodes.index', compact('episodes'));
    }

    public function create(): View
    {
        $animeSeasons = AnimeSeason::with('anime')
            ->orderBy('anime_id')
            ->orderBy('number')
            ->get();

        return view('admin.episodes.create', compact('animeSeasons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'anime_season_id' => ['required', 'exists:anime_seasons,id'],
            'number'          => ['required', 'integer', 'min:1'],
            'title'           => ['nullable', 'string', 'max:255'],
            'synopsis'        => ['nullable', 'string'],
            'air_date'        => ['nullable', 'date'],
        ]);

        $data['user_id'] = auth()->id();

        Episode::create($data);

        return redirect()->route('admin.episodes.index')
            ->with('status', 'Episodio creado correctamente.');
    }

    public function show(Episode $episode): RedirectResponse
    {
        return redirect()->route('animes.show', [
            'anime' => $episode->animeSeason->anime,
            'tab'   => 'episodes',
        ]);
    }

    public function edit(Episode $episode): View
    {
        $animeSeasons = AnimeSeason::with('anime')
            ->orderBy('anime_id')
            ->orderBy('number')
            ->get();

        return view('admin.episodes.edit', compact('episode', 'animeSeasons'));
    }

    public function update(Request $request, Episode $episode): RedirectResponse
    {
        $data = $request->validate([
            'anime_season_id' => ['required', 'exists:anime_seasons,id'],
            'number'          => ['required', 'integer', 'min:1'],
            'title'           => ['nullable', 'string', 'max:255'],
            'synopsis'        => ['nullable', 'string'],
            'air_date'        => ['nullable', 'date'],
        ]);

        $episode->update($data);

        return redirect()->route('admin.episodes.index')
            ->with('status', 'Episodio actualizado correctamente.');
    }

    public function destroy(Episode $episode): RedirectResponse
    {
        $episode->delete();

        return redirect()->route('admin.episodes.index')
            ->with('status', 'Episodio eliminado.');
    }
}