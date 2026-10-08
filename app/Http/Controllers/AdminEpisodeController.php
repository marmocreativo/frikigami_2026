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
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        // Temporadas de serie del anime elegido (para el segundo select)
        $animeSeasons = $currentAnime
            ? AnimeSeason::where('anime_id', $currentAnime->id)->orderBy('number')->get()
            : collect();

        // Solo vale si la temporada pertenece al anime elegido
        $currentSeason = $animeSeasons->firstWhere('id', (int) $request->query('anime_season'));

        $episodes = Episode::with('animeSeason.anime')
            ->when($currentSeason, fn ($query, $season) => $query->where('anime_season_id', $season->id))
            ->when(
                ! $currentSeason && $currentAnime,
                fn ($query) => $query->whereHas('animeSeason', fn ($s) => $s->where('anime_id', $currentAnime->id))
            )
            ->when(
                $currentAnime,
                fn ($query) => $query->orderBy('anime_season_id')->orderBy('number'),
                fn ($query) => $query->latest()
            )
            ->paginate(30)
            ->withQueryString();

        return view('admin.episodes.index', compact('episodes', 'animes', 'currentAnime', 'animeSeasons', 'currentSeason'));
    }

    public function create(Request $request): View
    {
        $currentAnime = Anime::where('slug', $request->query('anime'))->first();

        $animeSeasons = AnimeSeason::with('anime')
            ->when($currentAnime, fn ($query, $anime) => $query->where('anime_id', $anime->id))
            ->orderBy('anime_id')
            ->orderBy('number')
            ->get();

        // Temporada indicada en la URL o, si el anime solo tiene una, esa
        $selectedAnimeSeason = $animeSeasons->firstWhere('id', (int) $request->query('anime_season'))
            ?? ($currentAnime && $animeSeasons->count() === 1 ? $animeSeasons->first() : null);

        $selectedAnimeSeasonId = $selectedAnimeSeason?->id;
        $nextNumber = $selectedAnimeSeason
            ? ($selectedAnimeSeason->episodes()->max('number') ?? 0) + 1
            : null;

        return view('admin.episodes.create', compact('animeSeasons', 'selectedAnimeSeasonId', 'nextNumber'));
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
        return redirect()->route('animes.episodes', $episode->animeSeason->anime);
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