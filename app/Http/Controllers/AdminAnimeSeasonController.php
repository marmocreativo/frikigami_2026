<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeSeason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnimeSeasonController extends Controller
{
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        $animeSeasons = AnimeSeason::with('anime')
            ->when($currentAnime, fn ($query, $anime) => $query->where('anime_id', $anime->id))
            ->orderBy('anime_id')
            ->orderBy('number')
            ->paginate(20)
            ->withQueryString();

        return view('admin.anime_seasons.index', compact('animeSeasons', 'animes', 'currentAnime'));
    }

    public function create(Request $request): View
    {
        $animes = Anime::orderBy('title')->get();
        $selectedAnimeId = $animes->firstWhere('slug', $request->query('anime'))?->id;

        return view('admin.anime_seasons.create', compact('animes', 'selectedAnimeId'));
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
        return redirect()->route('animes.episodes', $animeSeason->anime);
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