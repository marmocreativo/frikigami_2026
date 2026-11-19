<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeSeason;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnimeSeasonController extends Controller
{
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        // Agrupadas por anime
        $groups = Anime::with(['season', 'animeSeasons' => fn ($query) => $query->with('season')->withCount('episodes')->orderBy('number')])
            ->whereHas('animeSeasons')
            ->when($currentAnime, fn ($query, $anime) => $query->where('id', $anime->id))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return view('admin.anime_seasons.index', compact('groups', 'animes', 'currentAnime'));
    }

    public function create(Request $request): View
    {
        $animes = Anime::orderBy('title')->get();
        $selectedAnimeId = $animes->firstWhere('slug', $request->query('anime'))?->id;

        $estrenos = Season::orderByDesc('year')->orderBy('name')->get();

        return view('admin.anime_seasons.create', compact('animes', 'selectedAnimeId', 'estrenos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'anime_id' => ['required', 'exists:animes,id'],
            'number'   => ['required', 'integer', 'min:1'],
            'title'    => ['nullable', 'string', 'max:255'],
            'year'     => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'season_id' => ['nullable', 'exists:seasons,id'],
        ]);

        AnimeSeason::create($data);

        return redirect()->route('admin.temporadas.index')
            ->with('status', 'Temporada creada correctamente.');
    }

    public function show(AnimeSeason $animeSeason): RedirectResponse
    {
        return redirect()->route('animes.episodios', $animeSeason->anime);
    }

    public function edit(AnimeSeason $animeSeason): View
    {
        $animes = Anime::orderBy('title')->get();

        $estrenos = Season::orderByDesc('year')->orderBy('name')->get();

        return view('admin.anime_seasons.edit', compact('animeSeason', 'animes', 'estrenos'));
    }

    public function update(Request $request, AnimeSeason $animeSeason): RedirectResponse
    {
        $data = $request->validate([
            'anime_id' => ['required', 'exists:animes,id'],
            'number'   => ['required', 'integer', 'min:1'],
            'title'    => ['nullable', 'string', 'max:255'],
            'year'     => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'season_id' => ['nullable', 'exists:seasons,id'],
        ]);

        $animeSeason->update($data);

        return redirect()->route('admin.temporadas.index')
            ->with('status', 'Temporada actualizada correctamente.');
    }

    public function destroy(AnimeSeason $animeSeason): RedirectResponse
    {
        $animeSeason->delete();

        return redirect()->route('admin.temporadas.index')
            ->with('status', 'Temporada eliminada.');
    }
}