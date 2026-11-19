<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\AnimeSeason;
use App\Models\Genre;
use App\Models\Season;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Lógica de franquicias: un anime "padre" con sus temporadas.
 * AniList trata cada temporada como un anime aparte; aquí las agrupamos.
 */
class FranchiseLinker
{
    /**
     * Anime (y temporada) de tu base que corresponde a un id de AniList.
     *
     * @return array{anime: Anime, temporada: ?AnimeSeason}|null
     */
    public function findByAnilistId(int $anilistId): ?array
    {
        if ($temporada = AnimeSeason::with('anime')->where('anilist_id', $anilistId)->first()) {
            return ['anime' => $temporada->anime, 'temporada' => $temporada];
        }

        if ($anime = Anime::where('anilist_id', $anilistId)->first()) {
            return ['anime' => $anime, 'temporada' => null];
        }

        return null;
    }

    /**
     * Crea la temporada 1 del anime si todavía no existe, y la deja con el id de AniList del anime.
     */
    public function ensureFirstSeason(Anime $anime): AnimeSeason
    {
        $first = $anime->animeSeasons()->where('number', 1)->first();

        if ($first) {
            if (! $first->anilist_id && $anime->anilist_id && ! AnimeSeason::where('anilist_id', $anime->anilist_id)->exists()) {
                $first->update(['anilist_id' => $anime->anilist_id]);
            }

            return $first;
        }

        $malTaken = $anime->mal_id && AnimeSeason::where('mal_id', $anime->mal_id)->exists();
        $anilistTaken = $anime->anilist_id && AnimeSeason::where('anilist_id', $anime->anilist_id)->exists();

        return $anime->animeSeasons()->create([
            'number' => 1,
            'year' => $anime->season?->year,
            'season_id' => $anime->season_id,
            'anilist_id' => $anilistTaken ? null : $anime->anilist_id,
            'mal_id' => $malTaken ? null : $anime->mal_id,
        ]);
    }

    /**
     * Asigna al anime los géneros de AniList que existan en tu base (por anilist_name).
     * No quita los que ya tenga.
     *
     * @param  string[]  $anilistGenres
     */
    public function syncGenres(Anime $anime, array $anilistGenres): int
    {
        if (! $anilistGenres) {
            return 0;
        }

        $ids = Genre::whereIn('anilist_name', $anilistGenres)->pluck('id')->all();
        $anime->genres()->syncWithoutDetaching($ids);

        return count($ids);
    }

    /**
     * Estreno de tu base que corresponde al item (solo si ya existe; no crea estrenos viejos).
     */
    public function seasonFor(array $item): ?Season
    {
        $estreno = $item['estreno'] ?? null;

        return $estreno
            ? Season::where('name', $estreno['name'])->where('year', $estreno['year'])->first()
            : null;
    }

    public function uniqueSlug(array $item): string
    {
        $base = Str::slug($item['title']) ?: 'anime-' . $item['anilist_id'];
        $year = $item['estreno']['year'] ?? null;

        foreach (array_filter([$base, $year ? $base . '-' . $year : null, $base . '-' . $item['anilist_id']]) as $candidate) {
            if (! Anime::where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $base . '-' . $item['anilist_id'];
    }

    /**
     * Agrega un item de AniList como temporada de un anime existente.
     *
     * @param  array  $item  item normalizado de AniListService
     */
    public function addSeason(Anime $anime, array $item, ?Season $estreno, ?AnimeSeason $prequel = null): AnimeSeason
    {
        $prequel ??= $this->ensureFirstSeason($anime);

        $taken = $anime->animeSeasons()->pluck('number')->all();
        $wanted = $prequel->number + 1;
        $number = in_array($wanted, $taken, true) ? max($taken) + 1 : $wanted;

        $malTaken = $item['mal_id'] && AnimeSeason::where('mal_id', $item['mal_id'])->exists();

        return $anime->animeSeasons()->create([
            'number' => $number,
            'year' => $estreno?->year ?? ($item['estreno']['year'] ?? null),
            'season_id' => $estreno?->id,
            'anilist_id' => $item['anilist_id'],
            'mal_id' => $malTaken ? null : $item['mal_id'],
        ]);
    }

    /**
     * Pasa $source a ser temporada(s) de $target y elimina $source.
     * Mueve temporadas (con sus episodios), personajes, staff y noticias.
     */
    public function mergeInto(Anime $source, Anime $target): int
    {
        return DB::transaction(function () use ($source, $target) {
            $this->ensureFirstSeason($source);

            $moved = 0;

            foreach ($source->animeSeasons()->orderBy('number')->get() as $temporada) {
                $taken = $target->animeSeasons()->pluck('number')->all();
                $number = in_array($temporada->number, $taken, true)
                    ? max($taken) + 1
                    : $temporada->number;

                // Si no tiene estreno propio, hereda el del anime que se absorbe
                $temporada->update([
                    'anime_id' => $target->id,
                    'number' => $number,
                    'season_id' => $temporada->season_id ?? $source->season_id,
                ]);
                $moved++;
            }

            // Tablas pivote: se copian al anime padre ignorando duplicados
            foreach (['anime_character', 'anime_person', 'anime_news', 'character_person'] as $pivot) {
                $rows = DB::table($pivot)->where('anime_id', $source->id)->get()
                    ->map(fn ($row) => array_merge((array) $row, ['anime_id' => $target->id]))
                    ->map(fn ($row) => array_diff_key($row, ['id' => true]))
                    ->all();

                if ($rows) {
                    DB::table($pivot)->insertOrIgnore($rows);
                }
            }

            $source->refresh()->delete();

            return $moved;
        });
    }
}
