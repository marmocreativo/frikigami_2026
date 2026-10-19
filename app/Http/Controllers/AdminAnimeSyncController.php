<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeSeason;
use App\Services\AniListSeriesService;
use App\Services\KitsuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Contracts\Translator;
use RuntimeException;

class AdminAnimeSyncController extends Controller
{
    private const FORMAT_LABELS = [
        'TV' => 'TV',
        'TV_SHORT' => 'TV corto',
        'ONA' => 'ONA',
        'MOVIE' => 'Película',
        'OVA' => 'OVA',
        'SPECIAL' => 'Especial',
    ];

    private const DEFAULT_FORMATS = ['TV', 'TV_SHORT', 'ONA'];

    public function __construct(
        private AniListSeriesService $series,
        private KitsuService $kitsu,
        private Translator $translator,
    ) {
    }

    public function show(Request $request, Anime $anime): View
    {
        $formats = $this->formats($request);
        $error = null;
        $rows = collect();
        $candidates = collect();
        $takenIds = [];

        try {
            if ($anime->anilist_id) {
                $rows = $this->seasonRows($anime, $formats);
            } else {
                $candidates = collect($this->series->search($anime->title));
                $takenIds = Anime::whereIn('anilist_id', $candidates->pluck('anilist_id'))->pluck('anilist_id')->all();
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        $formatLabels = self::FORMAT_LABELS;

        return view('admin.animes.anilist', compact(
            'anime', 'rows', 'candidates', 'takenIds', 'formats', 'formatLabels', 'error'
        ));
    }

    public function refresh(Anime $anime): RedirectResponse
    {
        if ($anime->anilist_id) {
            $this->series->forget($anime->anilist_id);
        }

        // También los episodios cacheados de sus temporadas
        $anime->animeSeasons()->get()
            ->each(fn ($season) => $this->kitsu->forget($season->mal_id, $season->anilist_id));

        return redirect()->route('admin.animes.anilist', $anime)
            ->with('status', 'Datos actualizados desde AniList.');
    }

    /**
     * Vincula el anime con una entrada de AniList (cuando aún no tiene anilist_id).
     */
    public function linkAnime(Request $request, Anime $anime): RedirectResponse
    {
        $data = $request->validate([
            'anilist_id' => ['required', 'integer', 'unique:animes,anilist_id'],
        ]);

        try {
            $item = $this->series->find((int) $data['anilist_id']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['anilist_id' => 'No se pudo consultar AniList: ' . $e->getMessage()]);
        }

        $malTaken = $item['mal_id'] && Anime::where('mal_id', $item['mal_id'])->where('id', '!=', $anime->id)->exists();

        $anime->update([
            'anilist_id' => $item['anilist_id'],
            'mal_id' => $malTaken ? $anime->mal_id : ($item['mal_id'] ?? $anime->mal_id),
        ]);

        return redirect()->route('admin.animes.anilist', $anime)
            ->with('status', "«{$anime->title}» vinculado con AniList.");
    }

    public function importSeasons(Request $request, Anime $anime): RedirectResponse
    {
        set_time_limit(600);

        $request->validate([
            'ids' => ['required', 'array', 'max:20'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Selecciona al menos una temporada para importar.',
        ]);

        try {
            $rows = $this->seasonRows($anime, $this->formats($request))
                ->keyBy(fn ($row) => $row['item']['anilist_id']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['ids' => 'No se pudo consultar AniList: ' . $e->getMessage()]);
        }

        $withEpisodes = $request->boolean('with_episodes');

        $created = 0;
        $skipped = 0;
        $episodesCreated = 0;
        $episodeErrors = 0;

        foreach ($request->input('ids') as $id) {
            $row = $rows->get((int) $id);

            if (! $row || $row['state'] !== 'missing' || AnimeSeason::where('anilist_id', $row['item']['anilist_id'])->exists()) {
                $skipped++;
                continue;
            }

            $item = $row['item'];
            $malTaken = $item['mal_id'] && AnimeSeason::where('mal_id', $item['mal_id'])->exists();

            $animeSeason = $anime->animeSeasons()->create([
                'number' => $this->freeNumber($anime, $row['number']),
                'title' => $row['title'],
                'year' => $item['year'],
                'anilist_id' => $item['anilist_id'],
                'mal_id' => $malTaken ? null : $item['mal_id'],
            ]);

            $created++;

            if ($withEpisodes) {
                try {
                    $episodesCreated += $this->importEpisodesFor($animeSeason)['created'];
                } catch (RuntimeException) {
                    $episodeErrors++;
                }

                usleep(500_000);
            }
        }

        $message = $created === 1 ? '1 temporada importada' : "{$created} temporadas importadas";

        if ($withEpisodes) {
            $message .= " con {$episodesCreated} episodios";
        }

        $message .= $skipped ? " ({$skipped} omitidas)." : '.';

        if ($episodeErrors) {
            $message .= " No se pudieron obtener los episodios de {$episodeErrors} temporada(s): usa «Importar episodios» para reintentar.";
        }

        return redirect()->route('admin.animes.anilist', $anime)->with('status', $message);
    }

    /**
     * Vincula una temporada que ya tenías (mismo número) con su entrada de AniList.
     */
    public function linkSeason(Request $request, Anime $anime): RedirectResponse
    {
        $data = $request->validate([
            'anime_season_id' => ['required', 'exists:anime_seasons,id'],
            'anilist_id' => ['required', 'integer', 'unique:anime_seasons,anilist_id'],
        ]);

        $animeSeason = $anime->animeSeasons()->findOrFail($data['anime_season_id']);

        try {
            $item = $this->series->find((int) $data['anilist_id']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['anilist_id' => 'No se pudo consultar AniList: ' . $e->getMessage()]);
        }

        $malTaken = $item['mal_id'] && AnimeSeason::where('mal_id', $item['mal_id'])->where('id', '!=', $animeSeason->id)->exists();

        $animeSeason->update([
            'anilist_id' => $item['anilist_id'],
            'mal_id' => $malTaken ? $animeSeason->mal_id : ($item['mal_id'] ?? $animeSeason->mal_id),
            'year' => $animeSeason->year ?? $item['year'],
        ]);

        return redirect()->route('admin.animes.anilist', $anime)
            ->with('status', "{$animeSeason->label} vinculada con AniList.");
    }

    /**
     * Importa (o completa) los episodios de una temporada ya vinculada.
     */
    public function importEpisodes(Anime $anime, AnimeSeason $animeSeason): RedirectResponse
    {
        abort_unless($animeSeason->anime_id === $anime->id, 404);

        set_time_limit(600);

        try {
            $result = $this->importEpisodesFor($animeSeason);
        } catch (RuntimeException $e) {
            return back()->withErrors(['episodes' => 'No se pudieron obtener los episodios: ' . $e->getMessage()]);
        }

        if ($result['total'] === 0) {
            return back()->withErrors([
                'episodes' => "{$animeSeason->label}: aún no hay datos de episodios disponibles.",
            ]);
        }

        return redirect()->route('admin.animes.anilist', $anime)->with(
            'status',
            "{$animeSeason->label}: {$result['created']} episodios nuevos y {$result['updated']} completados (fuente: {$result['source']})."
        );
    }

    /**
     * Crea los episodios que faltan y rellena título, sinopsis o fecha vacíos de los existentes.
     * Usa Kitsu y, si no hay datos, el calendario de AniList.
     *
     * @return array{created: int, updated: int, total: int, source: string}
     */
    private function importEpisodesFor(AnimeSeason $animeSeason): array
    {
        if (! $animeSeason->mal_id && ! $animeSeason->anilist_id) {
            throw new RuntimeException('La temporada no está vinculada a AniList.');
        }

        $source = 'Kitsu';

        try {
            $episodes = $this->kitsu->episodes($animeSeason->mal_id, $animeSeason->anilist_id);
        } catch (RuntimeException) {
            $episodes = [];
        }

        if ($episodes === [] && $animeSeason->anilist_id) {
            $source = 'AniList';
            $episodes = $this->series->airingSchedule($animeSeason->anilist_id);
        }

        $existing = $animeSeason->episodes()->get()->keyBy('number');

        // Sinopsis al español: solo episodios nuevos o con la sinopsis vacía
        $pending = collect($episodes)
            ->filter(fn ($episode) => $episode['number'] >= 1
                && ! empty($episode['synopsis'])
                && empty($existing->get($episode['number'])?->synopsis))
            ->mapWithKeys(fn ($episode) => [$episode['number'] => $episode['synopsis']])
            ->all();

        $translated = $pending ? ($this->translator->toSpanish($pending) ?? []) : [];

        $created = 0;
        $updated = 0;

        foreach ($episodes as $episode) {
            if ($episode['number'] < 1) {
                continue;
            }

            $title = $episode['title'] ? Str::limit($episode['title'], 255, '') : null;
            $synopsis = $translated[$episode['number']] ?? ($episode['synopsis'] ?? null);
            $local = $existing->get($episode['number']);

            if ($local) {
                $changes = [];

                if (! $local->title && $title) {
                    $changes['title'] = $title;
                }

                if (! $local->synopsis && $synopsis) {
                    $changes['synopsis'] = $synopsis;
                }

                if (! $local->air_date && $episode['air_date']) {
                    $changes['air_date'] = $episode['air_date'];
                }

                if ($changes) {
                    $local->update($changes);
                    $updated++;
                }

                continue;
            }

            $animeSeason->episodes()->create([
                'user_id' => auth()->id(),
                'number' => $episode['number'],
                'title' => $title,
                'synopsis' => $synopsis,
                'air_date' => $episode['air_date'],
            ]);

            $created++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($episodes),
            'source' => $source,
        ];
    }

    /**
     * Entradas de la franquicia cruzadas con las temporadas que ya tienes.
     */
    private function seasonRows(Anime $anime, array $formats): Collection
    {
        $items = collect($this->series->franchise($anime->anilist_id))
            ->filter(fn ($item) => $item['format'] === null || in_array($item['format'], $formats, true))
            ->values();

        $local = $anime->animeSeasons()->withCount('episodes')->get();
        $byAnilist = $local->whereNotNull('anilist_id')->keyBy('anilist_id');
        $byNumber = $local->whereNull('anilist_id')->keyBy('number');

        return $items->map(function ($item, $index) use ($anime, $byAnilist, $byNumber) {
            $number = $index + 1;
            $animeSeason = $byAnilist->get($item['anilist_id']);
            $state = 'linked';

            if (! $animeSeason) {
                $animeSeason = $byNumber->get($number);
                $state = $animeSeason ? 'possible' : 'missing';
            }

            return [
                'item' => $item,
                'number' => $number,
                'title' => $this->seasonTitle($item, $anime),
                'state' => $state,
                'local' => $animeSeason,
            ];
        });
    }

    /**
     * "Demon Slayer: Kimetsu no Yaiba Entertainment District Arc" → "Entertainment District Arc".
     * Si queda solo "2nd Season" o similar, devuelve null (se mostrará "Temporada 2").
     */
    private function seasonTitle(array $item, Anime $anime): ?string
    {
        $title = $item['title'];
        $base = $anime->title;

        if (Str::startsWith(Str::lower($title), Str::lower($base))) {
            $title = trim(mb_substr($title, mb_strlen($base)), " \t\n\r:-–—");
        }

        if ($title === '' || Str::lower($title) === Str::lower($base)) {
            return null;
        }

        if (preg_match('/^(\d+(st|nd|rd|th)\s+season|season\s+\d+|part\s+\d+)$/i', $title)) {
            return null;
        }

        return $title;
    }

    private function freeNumber(Anime $anime, int $wanted): int
    {
        $taken = $anime->animeSeasons()->pluck('number')->all();

        return in_array($wanted, $taken, true) ? max($taken) + 1 : $wanted;
    }

    private function formats(Request $request): array
    {
        $requested = array_intersect((array) $request->input('formats', []), array_keys(self::FORMAT_LABELS));

        return $requested ?: self::DEFAULT_FORMATS;
    }
}