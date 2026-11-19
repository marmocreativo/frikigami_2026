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

    public function estreno(Season $estreno): View
    {
        $animes = $this->catalogQuery()
            ->where(fn ($q) => $q->where('season_id', $estreno->id)
                ->orWhereHas('animeSeasons', fn ($t) => $t->where('season_id', $estreno->id)))
            ->with(['animeSeasons' => fn ($t) => $t->where('season_id', $estreno->id)])
            ->paginate(12)->withQueryString();

        return view('animes.index', [
            'animes' => $animes,
            'genres' => Genre::orderBy('name')->get(),
            'seasons' => Season::orderByDesc('year')->orderBy('name')->get(),
            'activeSeason' => $estreno,
        ]);
    }

    public function genero(Genre $genre): View
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
            'cast' => 'animes.reparto',
            'characters' => 'animes.personajes',
            'episodes' => 'animes.episodios',
            'news' => 'animes.noticias',
        ];
        if (isset($legacy[request('tab')])) {
            return redirect()->route($legacy[request('tab')], $anime, 301);
        }

        $anime->load('season', 'genres')->loadCount(['episodes', 'animeSeasons', 'characters', 'staff']);

        // Muestras para la ficha; cada una enlaza a su página completa
        $temporadas = $anime->animeSeasons()->with('season')->withCount('episodes')->orderBy('number')->get();

        $staffSample = $anime->staff()->orderBy('people.name')->take(6)->get();

        $charactersSample = $anime->characters()
            ->with(['voiceActors' => fn ($q) => $q->wherePivot('anime_id', $anime->id)])
            ->orderBy('characters.name')
            ->take(6)
            ->get();

        // Últimos episodios: temporada más reciente primero
        $episodesSample = $anime->episodes()
            ->with('animeSeason')
            ->orderByDesc('anime_seasons.number')
            ->orderByDesc('episodes.number')
            ->take(5)
            ->get();

        $newsSample = $anime->news()->published()->with('user')->latest('published_at')->take(3)->get();
        $newsCount = $anime->news()->published()->count();

        return $this->renderTab('animes.show', $anime, compact(
            'temporadas', 'staffSample', 'charactersSample', 'episodesSample', 'newsSample', 'newsCount'
        ));
    }

    public function temporadas(Anime $anime): View
    {
        $anime->load('season', 'genres');

        $temporadas = $anime->animeSeasons()->with('season')->withCount('episodes')->orderBy('number')->get();

        return $this->renderTab('animes.temporadas', $anime, compact('temporadas'));
    }

    public function reparto(Anime $anime): View
    {
        $anime->load([
            'season', 'genres', 'staff',
            'characters.voiceActors' => fn ($q) => $q->wherePivot('anime_id', $anime->id),
        ]);

        return $this->renderTab('animes.cast', $anime);
    }

    public function personajes(Anime $anime): View
    {
        $anime->load([
            'season', 'genres',
            'characters.voiceActors' => fn ($q) => $q->wherePivot('anime_id', $anime->id),
        ]);

        return $this->renderTab('animes.characters', $anime);
    }

    public function episodios(Anime $anime): View
    {
        $anime->load('season', 'genres', 'animeSeasons.episodes');

        return $this->renderTab('animes.episodes', $anime);
    }

    public function temporada(Anime $anime, int $numero): View
    {
        $anime->load('season', 'genres');

        $temporada = $anime->animeSeasons()->with('episodes')->where('number', $numero)->firstOrFail();

        return $this->renderTab('animes.temporada', $anime, compact('temporada'));
    }

    public function episodio(Anime $anime, int $numero, int $episodio): View
    {
        $anime->load('season', 'genres');

        $temporada = $anime->animeSeasons()->where('number', $numero)->firstOrFail();
        $episodio = $temporada->episodes()->where('number', $episodio)->firstOrFail();

        return $this->renderTab('animes.episodio', $anime, compact('temporada', 'episodio'));
    }

    public function noticias(Anime $anime): View
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
}
