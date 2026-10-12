<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Season;
use App\Services\AniListService;
use App\Services\GeminiTranslator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AdminAniListController extends Controller
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

    private const DEFAULT_FORMATS = ['TV', 'TV_SHORT', 'ONA', 'MOVIE'];

    public function __construct(
        private AniListService $anilist,
        private GeminiTranslator $translator,
    ) {
    }

    public function show(Request $request, Season $season): View
    {
        $formats = $this->formats($request);
        $error = null;
        $allRows = collect();

        try {
            $allRows = $this->matchWithLocal(collect($this->anilist->season($season)));
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        $rows = $allRows
            ->filter(fn ($row) => $row['item']['format'] === null || in_array($row['item']['format'], $formats, true))
            ->values();

        $counts = [
            'total' => $rows->count(),
            'linked' => $rows->where('state', 'linked')->count(),
            'possible' => $rows->where('state', 'possible')->count(),
            'missing' => $rows->where('state', 'missing')->count(),
        ];

        // Animes de tu base asignados a esta temporada que no aparecen en AniList
        $unmatchedLocal = $error
            ? collect()
            : Anime::where('season_id', $season->id)
                ->whereNotIn('id', $allRows->pluck('anime.id')->filter()->all())
                ->orderBy('title')
                ->get(['id', 'title', 'slug']);

        $formatLabels = self::FORMAT_LABELS;

        return view('admin.seasons.anilist', compact(
            'season', 'rows', 'counts', 'formats', 'formatLabels', 'unmatchedLocal', 'error'
        ));
    }

    public function refresh(Season $season): RedirectResponse
    {
        $this->anilist->forget($season);

        return redirect()->route('admin.seasons.anilist', $season)
            ->with('status', 'Datos actualizados desde AniList.');
    }

    public function link(Request $request, Season $season): RedirectResponse
    {
        $data = $request->validate([
            'anime_id' => ['required', 'exists:animes,id'],
            'anilist_id' => ['required', 'integer', 'unique:animes,anilist_id'],
        ]);

        $item = collect($this->anilist->season($season))->firstWhere('anilist_id', (int) $data['anilist_id']);
        abort_unless($item, 404);

        $anime = Anime::findOrFail($data['anime_id']);

        $malTaken = $item['mal_id'] && Anime::where('mal_id', $item['mal_id'])->where('id', '!=', $anime->id)->exists();

        $anime->update([
            'anilist_id' => $item['anilist_id'],
            'mal_id' => $malTaken ? $anime->mal_id : ($item['mal_id'] ?? $anime->mal_id),
        ]);

        return redirect()->route('admin.seasons.anilist', $season)
            ->with('status', "«{$anime->title}» vinculado con AniList.");
    }

    public function import(Request $request, Season $season): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:15'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Selecciona al menos un anime para importar.',
            'ids.max' => 'Importa máximo 15 por vez (se descargan las portadas).',
        ]);

        $items = collect($this->anilist->season($season))->keyBy('anilist_id');

        // Solo los que existen en AniList y aún no están en tu base
        $toImport = collect($data['ids'])
            ->map(fn ($id) => $items->get((int) $id))
            ->filter(fn ($item) => $item && ! Anime::where('anilist_id', $item['anilist_id'])->exists())
            ->values();

        $skipped = count($data['ids']) - $toImport->count();

        // Sinopsis al español, todas en una sola petición a Gemini
        $synopses = $toImport
            ->filter(fn ($item) => $item['synopsis'])
            ->mapWithKeys(fn ($item) => [$item['anilist_id'] => $item['synopsis']])
            ->all();

        $translated = $synopses ? $this->translator->toSpanish($synopses) : [];
        $translationFailed = $synopses && $translated === null;
        $translated ??= [];

        foreach ($toImport as $item) {
            $slug = $this->uniqueSlug($item, $season);
            $malTaken = $item['mal_id'] && Anime::where('mal_id', $item['mal_id'])->exists();

            Anime::create([
                'title' => $item['title'],
                'slug' => $slug,
                'synopsis' => $translated[$item['anilist_id']] ?? $item['synopsis'],
                'cover_image' => $this->anilist->downloadCover($item['cover'], $slug),
                'status' => AniListService::mapStatus($item['status']),
                'season_id' => $season->id,
                'anilist_id' => $item['anilist_id'],
                'mal_id' => $malTaken ? null : $item['mal_id'],
            ]);
        }

        $created = $toImport->count();

        $message = $created === 1 ? '1 anime importado' : "{$created} animes importados";
        $message .= $skipped ? " ({$skipped} omitidos por ya existir)." : '.';

        if ($translationFailed) {
            $message .= ' Las sinopsis quedaron en inglés: Gemini no está configurado o no respondió.';
        }

        return redirect()->route('admin.seasons.anilist', $season)->with('status', $message);
    }

    /**
     * Cruza cada resultado de AniList con tu base: vinculado, posible o faltante.
     */
    private function matchWithLocal(Collection $items): Collection
    {
        $local = Anime::all(['id', 'title', 'slug', 'season_id', 'anilist_id', 'mal_id']);

        $byAnilist = $local->whereNotNull('anilist_id')->keyBy('anilist_id');
        $byMal = $local->whereNotNull('mal_id')->keyBy('mal_id');
        $byTitle = $local->whereNull('anilist_id')->keyBy(fn ($anime) => AniListService::normalize($anime->title));

        return $items->map(function ($item) use ($byAnilist, $byMal, $byTitle) {
            $anime = $byAnilist->get($item['anilist_id'])
                ?? ($item['mal_id'] ? $byMal->get($item['mal_id']) : null);

            if ($anime) {
                return ['item' => $item, 'state' => 'linked', 'anime' => $anime];
            }

            foreach ($item['titles'] as $title) {
                $key = AniListService::normalize($title);

                if (strlen($key) >= 4 && $byTitle->has($key)) {
                    return ['item' => $item, 'state' => 'possible', 'anime' => $byTitle->get($key)];
                }
            }

            return ['item' => $item, 'state' => 'missing', 'anime' => null];
        });
    }

    private function formats(Request $request): array
    {
        $requested = array_intersect((array) $request->query('formats', []), array_keys(self::FORMAT_LABELS));

        return $requested ?: self::DEFAULT_FORMATS;
    }

    private function uniqueSlug(array $item, Season $season): string
    {
        $base = Str::slug($item['title']) ?: 'anime-' . $item['anilist_id'];

        foreach ([$base, $base . '-' . $season->year, $base . '-' . $item['anilist_id']] as $candidate) {
            if (! Anime::where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $base . '-' . $item['anilist_id'];
    }
}