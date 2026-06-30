<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnimeRequest;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Season;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnimeController extends Controller
{
    public function index(): View
    {
        $animes = Anime::with('season', 'genres')
            ->latest()
            ->paginate(12);
        $genres = Genre::orderBy('name')->get();
        $seasons = Season::orderBy('name')->get();

        return view('animes.index', compact('animes', 'genres', 'seasons'));
    }

    public function show(Anime $anime): View
    {
        $anime->load('season', 'genres', 'animeSeasons.episodes', 'characters.voiceActors', 'staff', 'news.user');

        $tab = request('tab', 'info');

        return view('animes.show', compact('anime', 'tab'));
    }

    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();
        $seasons = Season::orderByDesc('year')->orderBy('name')->get();

        return view('animes.create', compact('genres', 'seasons'));
    }

    public function store(StoreAnimeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('animes', 'public');
        }

        $anime = Anime::create($data);
        $anime->genres()->sync($data['genres'] ?? []);

        return redirect()->route('animes.show', $anime)
            ->with('status', 'Anime creado correctamente.');
    }

    public function edit(Anime $anime): View
    {
        $genres = Genre::orderBy('name')->get();
        $seasons = Season::orderByDesc('year')->orderBy('name')->get();
        $anime->load('genres');

        return view('animes.edit', compact('anime', 'genres', 'seasons'));
    }

    public function update(StoreAnimeRequest $request, Anime $anime): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('animes', 'public');
        }

        $anime->update($data);
        $anime->genres()->sync($data['genres'] ?? []);

        return redirect()->route('animes.show', $anime)
            ->with('status', 'Anime actualizado correctamente.');
    }

    public function destroy(Anime $anime): RedirectResponse
    {
        $anime->delete();

        return redirect()->route('animes.index')
            ->with('status', 'Anime eliminado.');
    }
}