<?php

namespace App\Http\Controllers;

use App\Contracts\Translator;
use App\Models\Anime;
use App\Services\AniListSeriesService;
use App\Services\AniListService;
use App\Services\FranchiseLinker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/**
 * Buscar un anime por nombre en AniList, ver su franquicia (precuelas y secuelas)
 * e importar una o varias entradas.
 */
class AdminAniListSearchController extends Controller
{
    private const FORMAT_LABELS = [
        'TV' => 'TV',
        'TV_SHORT' => 'TV corto',
        'ONA' => 'ONA',
        'MOVIE' => 'Película',
        'OVA' => 'OVA',
        'SPECIAL' => 'Especial',
        'MUSIC' => 'Música',
    ];

    public function __construct(
        private AniListSeriesService $series,
        private AniListService $anilist,
        private FranchiseLinker $franchises,
        private Translator $translator,
    ) {
    }

    public function buscar(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $results = collect();
        $error = null;

        if ($q !== '') {
            try {
                $results = collect($this->series->search($q))->map(fn ($item) => $item + [
                    'local' => $this->franchises->findByAnilistId($item['anilist_id']),
                ]);
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }

        return view('admin.anilist.buscar', [
            'q' => $q,
            'results' => $results,
            'error' => $error,
            'formatLabels' => self::FORMAT_LABELS,
        ]);
    }

    public function franquicia(Request $request, int $id): View
    {
        if ($request->boolean('actualizar')) {
            $this->series->forget($id);
        }

        $error = null;
        $entries = collect();

        try {
            $entries = collect($this->series->franchise($id))->map(fn ($item) => $item + [
                'local' => $this->franchises->findByAnilistId($item['anilist_id']),
            ]);
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        // Si alguna entrada ya está en tu base, esa es la candidata a anime padre
        $parentDefault = $entries->first(fn ($e) => $e['local'])['local']['anime'] ?? null;

        return view('admin.anilist.franquicia', [
            'rootId' => $id,
            'entries' => $entries,
            'error' => $error,
            'parentDefault' => $parentDefault,
            'animes' => Anime::orderBy('title')->get(['id', 'title']),
            'formatLabels' => self::FORMAT_LABELS,
        ]);
    }

    public function importar(Request $request, int $id): RedirectResponse
    {
        set_time_limit(600);

        $data = $request->validate([
            'ids' => ['required', 'array', 'max:15'],
            'ids.*' => ['integer'],
            'modo' => ['required', 'in:franquicia,separados'],
            'parent_id' => ['nullable', 'exists:animes,id'],
        ], [
            'ids.required' => 'Selecciona al menos una entrada para importar.',
            'ids.max' => 'Importa máximo 15 por vez (se descargan las portadas).',
        ]);

        try {
            $franchise = collect($this->series->franchise($id))->keyBy('anilist_id');

            // Solo las que existen en la franquicia, aún no están en tu base, en orden de estreno
            $selected = collect($data['ids'])
                ->map(fn ($anilistId) => $franchise->get((int) $anilistId))
                ->filter(fn ($item) => $item && ! $this->franchises->findByAnilistId($item['anilist_id']))
                ->sortBy('sort_date')
                ->values();

            $details = $selected->map(function ($item, $index) {
                if ($index > 0) {
                    usleep(700_000); // límite de peticiones de AniList
                }

                return $this->anilist->media($item['anilist_id']);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['ids' => 'No se pudo consultar AniList: ' . $e->getMessage()])->withInput();
        }

        $skipped = count($data['ids']) - $selected->count();

        $parent = ($data['modo'] === 'franquicia' && ! empty($data['parent_id']))
            ? Anime::find($data['parent_id'])
            : null;

        // Sinopsis al español: solo las de los animes que se van a crear
        $willCreate = $details->values()->filter(
            fn ($item, $index) => $data['modo'] === 'separados' || ($parent === null && $index === 0)
        );
        $synopses = $willCreate->filter(fn ($item) => $item['synopsis'])
            ->mapWithKeys(fn ($item) => [$item['anilist_id'] => $item['synopsis']])->all();
        $translated = $synopses ? $this->translator->toSpanish($synopses) : [];
        $translationFailed = $synopses && $translated === null;
        $translated ??= [];

        $animesCreated = 0;
        $seasonsAdded = 0;
        $firstAnime = null;

        foreach ($details->values() as $index => $item) {
            $estreno = $this->franchises->seasonFor($item);
            $createAnime = $data['modo'] === 'separados' || ($parent === null && $index === 0);

            if (! $createAnime) {
                $this->franchises->addSeason($parent, $item, $estreno);
                $seasonsAdded++;
                continue;
            }

            $slug = $this->franchises->uniqueSlug($item);
            $malTaken = $item['mal_id'] && Anime::where('mal_id', $item['mal_id'])->exists();

            $anime = Anime::create([
                'title' => $item['title'],
                'slug' => $slug,
                'synopsis' => $translated[$item['anilist_id']] ?? $item['synopsis'],
                'cover_image' => $this->anilist->downloadCover($item['cover'], $slug),
                'status' => AniListService::mapStatus($item['status']),
                'season_id' => $estreno?->id,
                'anilist_id' => $item['anilist_id'],
                'mal_id' => $malTaken ? null : $item['mal_id'],
            ]);

            $this->franchises->ensureFirstSeason($anime->load('season'));
            $this->franchises->syncGenres($anime, $item['genres'] ?? []);
            $animesCreated++;
            $firstAnime ??= $anime;

            if ($data['modo'] === 'franquicia') {
                $parent = $anime;
            }
        }

        $message = [];
        if ($animesCreated) {
            $message[] = $animesCreated === 1 ? '1 anime creado' : "{$animesCreated} animes creados";
        }
        if ($seasonsAdded) {
            $message[] = $seasonsAdded === 1 ? '1 temporada agregada' : "{$seasonsAdded} temporadas agregadas";
        }
        $text = ($message ? implode(' y ', $message) : 'No se importó nada')
            . ($skipped ? " ({$skipped} omitidas por ya existir)." : '.');

        if ($translationFailed) {
            $text .= ' Las sinopsis quedaron en inglés: el traductor no está configurado o no respondió.';
        }

        $destino = $parent ?? $firstAnime;

        return $destino
            ? redirect()->route('admin.animes.edit', $destino)->with('status', $text)
            : redirect()->route('admin.anilist.franquicia', $id)->with('status', $text);
    }
}
