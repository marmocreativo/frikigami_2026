<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeSeason;
use App\Models\Episode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        // Agrupados por anime. Los episodios solo se cargan al filtrar por anime;
        // sin filtro se muestran las temporadas con su conteo.
        $groups = Anime::with([
                'season',
                'animeSeasons' => fn ($query) => $query
                    ->when($currentSeason, fn ($q) => $q->where('id', $currentSeason->id))
                    ->withCount('episodes')
                    ->when($currentAnime, fn ($q) => $q->with(['episodes' => fn ($e) => $e->orderBy('number')]))
                    ->orderBy('number'),
            ])
            ->whereHas('animeSeasons.episodes')
            ->when($currentAnime, fn ($query, $anime) => $query->where('id', $anime->id))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return view('admin.episodes.index', compact('groups', 'animes', 'currentAnime', 'animeSeasons', 'currentSeason'));
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
            'thumbnail'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'thumbnail.image' => 'El archivo debe ser una imagen.',
            'thumbnail.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'thumbnail.max' => 'La imagen no puede pesar más de 4 MB.',
        ]);

        $data['user_id'] = auth()->id();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('episodes', 'public');
        }

        Episode::create($data);

        return redirect()->route('admin.episodios.index')
            ->with('status', 'Episodio creado correctamente.');
    }

    public function show(Episode $episode): RedirectResponse
    {
        return redirect()->route('animes.episodios', $episode->animeSeason->anime);
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
            'thumbnail'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'thumbnail.image' => 'El archivo debe ser una imagen.',
            'thumbnail.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'thumbnail.max' => 'La imagen no puede pesar más de 4 MB.',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($episode->thumbnail) {
                Storage::disk('public')->delete($episode->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('episodes', 'public');
        } else {
            unset($data['thumbnail']);

            // Casilla "Quitar imagen"
            if ($request->boolean('remove_thumbnail') && $episode->thumbnail) {
                Storage::disk('public')->delete($episode->thumbnail);
                $data['thumbnail'] = null;
            }
        }

        $episode->update($data);

        return redirect()->route('admin.episodios.index')
            ->with('status', 'Episodio actualizado correctamente.');
    }

    public function destroy(Episode $episode): RedirectResponse
    {
        if ($episode->thumbnail) {
            Storage::disk('public')->delete($episode->thumbnail);
        }

        $episode->delete();

        return redirect()->route('admin.episodios.index')
            ->with('status', 'Episodio eliminado.');
    }
}