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
    private function catalogQuery()
    {
        return Anime::with('season', 'genres')
            ->when(request('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->when(request('genre'), fn ($query, $slug) => $query->whereHas('genres', fn ($g) => $g->where('slug', $slug)))
            ->when(request('season'), fn ($query, $id) => $query->where('season_id', $id))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest();
    }

    public function index(): View
    {
        $animes = $this->catalogQuery()->paginate(12)->withQueryString();
        $genres = Genre::orderBy('name')->get();
        $seasons = Season::orderByDesc('year')->orderBy('name')->get();

        return view('animes.index', compact('animes', 'genres', 'seasons'));
    }

    public function season(Season $temporada): View
    {
        $animes = $this->catalogQuery()
            ->where('season_id', $temporada->id)
            ->paginate(12)->withQueryString();

        return view('animes.index', [
            'animes' => $animes,
            'genres' => Genre::orderBy('name')->get(),
            'seasons' => Season::orderByDesc('year')->orderBy('name')->get(),
            'activeSeason' => $temporada,
        ]);
    }

    public function genre(Genre $genre): View
    {
        $animes = $this->catalogQuery()
            ->whereHas('genres', fn ($q) => $q->where('genres.id', $genre->id))
            ->paginate(12)->withQueryString();

        return view('animes.index', [
            'animes' => $animes,
            'genres' => Genre::orderBy('name')->get(),
            'seasons' => Season::orderByDesc('year')->orderBy('name')->get(),
            'activeGenre' => $genre,
        ]);
    }

    public function show(Anime $anime): View|RedirectResponse
    {
        // Compatibilidad con los links viejos ?tab=
        $legacy = [
            'cast' => 'animes.cast',
            'characters' => 'animes.characters',
            'episodes' => 'animes.episodes',
            'news' => 'animes.news',
        ];
        if (isset($legacy[request('tab')])) {
            return redirect()->route($legacy[request('tab')], $anime, 301);
        }

        $anime->load('season', 'genres')->loadCount(['episodes', 'animeSeasons']);

        return $this->renderTab('animes.show', $anime);
    }

    public function cast(Anime $anime): View
    {
        $anime->load([
            'season', 'genres', 'staff',
            'characters.voiceActors' => fn ($q) => $q->wherePivot('anime_id', $anime->id),
        ]);

        return $this->renderTab('animes.cast', $anime);
    }

    public function characters(Anime $anime): View
    {
        $anime->load([
            'season', 'genres',
            'characters.voiceActors' => fn ($q) => $q->wherePivot('anime_id', $anime->id),
        ]);

        return $this->renderTab('animes.characters', $anime);
    }

    public function episodes(Anime $anime): View
    {
        $anime->load('season', 'genres', 'animeSeasons.episodes');

        return $this->renderTab('animes.episodes', $anime);
    }

    public function news(Anime $anime): View
    {
        $anime->load('season', 'genres');

        $news = $anime->news()
            ->published()
            ->with('user')
            ->latest('published_at')
            ->paginate(10);

        return $this->renderTab('animes.news', $anime, compact('news'));
    }

    private function renderTab(string $view, Anime $anime, array $data = []): View
    {
        $sameSeason = Anime::where('season_id', $anime->season_id)
            ->where('id', '!=', $anime->id)
            ->when(! $anime->season_id, fn ($q) => $q->whereRaw('1 = 0'))
            ->take(5)
            ->get();

        $sameGenre = Anime::whereHas('genres', fn ($q) => $q->whereIn('genres.id', $anime->genres->pluck('id')))
            ->where('id', '!=', $anime->id)
            ->take(5)
            ->get();

        return view($view, array_merge(compact('anime', 'sameSeason', 'sameGenre'), $data));
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