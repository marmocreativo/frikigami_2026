<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Person;
use App\Services\AniListCastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Contracts\Translator;
use RuntimeException;

class AdminAnimeCastSyncController extends Controller
{
    private const STAFF_ROLES = [
        'Original Creator' => 'Autor original',
        'Original Story' => 'Historia original',
        'Director' => 'Director',
        'Chief Director' => 'Director en jefe',
        'Assistant Director' => 'Director asistente',
        'Series Composition' => 'Composición de la serie',
        'Script' => 'Guion',
        'Screenplay' => 'Guion',
        'Character Design' => 'Diseño de personajes',
        'Original Character Design' => 'Diseño original de personajes',
        'Music' => 'Música',
        'Sound Director' => 'Director de sonido',
        'Art Director' => 'Director de arte',
        'Chief Animation Director' => 'Director de animación en jefe',
        'Animation Director' => 'Director de animación',
        'Director of Photography' => 'Director de fotografía',
        'Color Design' => 'Diseño de color',
        'Editing' => 'Edición',
        'Producer' => 'Productor',
        'Storyboard' => 'Storyboard',
        'Episode Director' => 'Director de episodio',
        'Theme Song Performance' => 'Canción temática',
    ];

    public function __construct(
        private AniListCastService $cast,
        private Translator $translator,
    ) {
    }

    public function show(Anime $anime): View|RedirectResponse
    {
        if (! $anime->anilist_id) {
            return redirect()->route('admin.animes.anilist', $anime)
                ->withErrors(['anilist_id' => 'Primero vincula el anime con AniList.']);
        }

        $error = null;
        $characters = collect();
        $staff = collect();

        try {
            $data = $this->cast->cast($anime->anilist_id);
            $characters = $this->characterRows($anime, $data['characters']);
            $staff = $this->staffRows($anime, $data['staff']);
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        return view('admin.animes.anilist_cast', compact('anime', 'characters', 'staff', 'error'));
    }

    public function refresh(Anime $anime): RedirectResponse
    {
        if ($anime->anilist_id) {
            $this->cast->forget($anime->anilist_id);
        }

        return redirect()->route('admin.animes.anilist.cast', $anime)
            ->with('status', 'Datos actualizados desde AniList.');
    }

    /**
     * Agrega al anime los personajes elegidos, con sus actores de voz.
     * Reutiliza lo que ya existe (por anilist_id o por nombre) en lugar de duplicar.
     */
    public function importCharacters(Request $request, Anime $anime): RedirectResponse
    {
        set_time_limit(600);

        $request->validate([
            'ids' => ['required', 'array', 'max:50'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Selecciona al menos un personaje.',
        ]);

        abort_unless($anime->anilist_id, 404);

        try {
            $items = collect($this->cast->cast($anime->anilist_id)['characters'])->keyBy('anilist_id');
        } catch (RuntimeException $e) {
            return back()->withErrors(['ids' => 'No se pudo consultar AniList: ' . $e->getMessage()]);
        }

        $selected = collect($request->input('ids'))
            ->map(fn ($id) => $items->get((int) $id))
            ->filter()
            ->values();

        $stats = $this->emptyStats();

        // Descripciones al español (solo las que se van a guardar), en una pasada
        $translations = $this->translateMissing($selected, Character::class, 'description', $stats);

        foreach ($selected as $item) {
            $item['description'] = $translations[$item['anilist_id']] ?? $item['description'];

            $character = $this->findOrCreateCharacter($item, $stats);

            $stats['attached'] += count($anime->characters()->syncWithoutDetaching([$character->id])['attached']);

            foreach ($item['voice_actors'] as $actor) {
                $person = $this->findOrCreatePerson($actor, ['is_voice_actor' => true], $stats);

                $stats['voices'] += DB::table('character_person')->insertOrIgnore([
                    'character_id' => $character->id,
                    'person_id' => $person->id,
                    'anime_id' => $anime->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.animes.anilist.cast', $anime)
            ->with('status', $this->summary($stats));
    }

    /**
     * Agrega al anime los integrantes del staff elegidos, con su rol.
     */
    public function importStaff(Request $request, Anime $anime): RedirectResponse
    {
        set_time_limit(600);

        $request->validate([
            'ids' => ['required', 'array', 'max:50'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Selecciona al menos una persona del staff.',
        ]);

        abort_unless($anime->anilist_id, 404);

        try {
            $items = collect($this->cast->cast($anime->anilist_id)['staff'])->keyBy('anilist_id');
        } catch (RuntimeException $e) {
            return back()->withErrors(['ids' => 'No se pudo consultar AniList: ' . $e->getMessage()]);
        }

        $selected = collect($request->input('ids'))
            ->map(fn ($id) => $items->get((int) $id))
            ->filter()
            ->values();

        $stats = $this->emptyStats();

        // Biografías al español (solo las que se van a guardar), en una pasada
        $translations = $this->translateMissing($selected, Person::class, 'bio', $stats);

        foreach ($selected as $item) {
            $item['description'] = $translations[$item['anilist_id']] ?? $item['description'];

            $person = $this->findOrCreatePerson($item, ['is_staff' => true], $stats);

            $stats['attached'] += DB::table('anime_person')->insertOrIgnore([
                'anime_id' => $anime->id,
                'person_id' => $person->id,
                'role' => $this->translateRole($item['role']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.animes.anilist.cast', $anime)
            ->with('status', $this->summary($stats));
    }

    private function findOrCreateCharacter(array $data, array &$stats): Character
    {
        $character = Character::where('anilist_id', $data['anilist_id'])->first();
        $alreadyLinked = (bool) $character;

        // Posible duplicado creado a mano: mismo nombre y aún sin vincular
        $character ??= Character::whereNull('anilist_id')->where('slug', Str::slug($data['name']))->first();

        if (! $character) {
            $character = new Character([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug(Character::class, $data['name'], $data['anilist_id']),
            ]);
            $stats['characters_created']++;
        } elseif (! $alreadyLinked) {
            $stats['characters_linked']++;
        }

        $character->anilist_id = $data['anilist_id'];
        $character->photo ??= $this->cast->downloadImage($data['image'], 'characters', $character->slug);
        $character->description ??= $data['description'];
        $character->save();

        return $character;
    }

    private function findOrCreatePerson(array $data, array $flags, array &$stats): Person
    {
        $person = Person::where('anilist_id', $data['anilist_id'])->first();
        $alreadyLinked = (bool) $person;

        $person ??= Person::whereNull('anilist_id')->where('slug', Str::slug($data['name']))->first();

        if (! $person) {
            $person = new Person([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug(Person::class, $data['name'], $data['anilist_id']),
            ]);
            $stats['people_created']++;
        } elseif (! $alreadyLinked) {
            $stats['people_linked']++;
        }

        $person->anilist_id = $data['anilist_id'];
        $person->photo ??= $this->cast->downloadImage($data['image'], 'people', $person->slug);
        $person->bio ??= $data['description'] ?? null;

        // Se suman las marcas; nunca se quita una que ya tenía
        foreach ($flags as $flag => $value) {
            $person->{$flag} = (bool) $person->{$flag} || $value;
        }

        $person->save();

        return $person;
    }

    private function uniqueSlug(string $model, string $name, int $anilistId): string
    {
        $base = Str::slug($name) ?: 'sin-nombre';

        return $model::where('slug', $base)->exists() ? "{$base}-{$anilistId}" : $base;
    }

    /**
     * Personajes de AniList cruzados con tu base:
     * linked = ya vinculado, possible = mismo nombre sin vincular, missing = no existe.
     */
    private function characterRows(Anime $anime, array $items): Collection
    {
        $local = Character::all(['id', 'name', 'slug', 'anilist_id']);
        $byAnilist = $local->whereNotNull('anilist_id')->keyBy('anilist_id');
        $bySlug = $local->whereNull('anilist_id')->keyBy('slug');

        $inAnime = $anime->characters()->pluck('characters.id')->flip();
        $peopleByAnilist = Person::whereNotNull('anilist_id')->pluck('id', 'anilist_id');
        $voiceLinks = DB::table('character_person')
            ->where('anime_id', $anime->id)
            ->get(['character_id', 'person_id'])
            ->mapWithKeys(fn ($row) => [$row->character_id . '-' . $row->person_id => true]);

        return collect($items)->map(function ($item) use ($byAnilist, $bySlug, $inAnime, $peopleByAnilist, $voiceLinks) {
            $localCharacter = $byAnilist->get($item['anilist_id']);
            $state = 'linked';

            if (! $localCharacter) {
                $localCharacter = $bySlug->get(Str::slug($item['name']));
                $state = $localCharacter ? 'possible' : 'missing';
            }

            $attached = $localCharacter && $inAnime->has($localCharacter->id);

            $voicesOk = $localCharacter && collect($item['voice_actors'])->every(function ($actor) use ($peopleByAnilist, $voiceLinks, $localCharacter) {
                $personId = $peopleByAnilist->get($actor['anilist_id']);

                return $personId && $voiceLinks->has($localCharacter->id . '-' . $personId);
            });

            return [
                'item' => $item,
                'state' => $state,
                'local' => $localCharacter,
                'done' => $state === 'linked' && $attached && $voicesOk,
            ];
        });
    }

    private function staffRows(Anime $anime, array $items): Collection
    {
        $local = Person::all(['id', 'name', 'slug', 'anilist_id']);
        $byAnilist = $local->whereNotNull('anilist_id')->keyBy('anilist_id');
        $bySlug = $local->whereNull('anilist_id')->keyBy('slug');

        $inAnime = DB::table('anime_person')->where('anime_id', $anime->id)->pluck('person_id')->flip();

        return collect($items)->map(function ($item) use ($byAnilist, $bySlug, $inAnime) {
            $localPerson = $byAnilist->get($item['anilist_id']);
            $state = 'linked';

            if (! $localPerson) {
                $localPerson = $bySlug->get(Str::slug($item['name']));
                $state = $localPerson ? 'possible' : 'missing';
            }

            return [
                'item' => $item,
                'role' => $this->translateRole($item['role']),
                'state' => $state,
                'local' => $localPerson,
                'done' => $localPerson && $inAnime->has($localPerson->id),
            ];
        });
    }

    private function translateRole(?string $role): string
    {
        $role = trim((string) $role);

        if ($role === '') {
            return 'Staff';
        }

        // Roles con detalle ("Key Animation (ep 1, 2)") se dejan como vienen
        if (str_contains($role, '(')) {
            return Str::limit($role, 255, '');
        }

        $parts = array_map(
            fn ($part) => self::STAFF_ROLES[trim($part)] ?? trim($part),
            explode(',', $role)
        );

        return Str::limit(implode(', ', $parts), 255, '');
    }

    private function emptyStats(): array
    {
        return [
            'characters_created' => 0,
            'characters_linked' => 0,
            'people_created' => 0,
            'people_linked' => 0,
            'attached' => 0,
            'voices' => 0,
            'translation_failed' => false,
        ];
    }

    /**
     * Traduce las descripciones de los registros que se van a crear o a completar.
     * Devuelve [anilist_id => texto en español]; marca el fallo en $stats.
     */
    private function translateMissing(Collection $selected, string $model, string $column, array &$stats): array
    {
        $existing = $model::whereIn('anilist_id', $selected->pluck('anilist_id'))->get()->keyBy('anilist_id');

        $texts = $selected
            ->filter(fn ($item) => $item['description'] && ! $existing->get($item['anilist_id'])?->{$column})
            ->mapWithKeys(fn ($item) => [$item['anilist_id'] => $item['description']])
            ->all();

        if ($texts === []) {
            return [];
        }

        $translated = $this->translator->toSpanish($texts);

        if ($translated === null) {
            $stats['translation_failed'] = true;

            return [];
        }

        return $translated;
    }

    private function summary(array $stats): string
    {
        $parts = [];

        if ($stats['characters_created']) {
            $parts[] = "{$stats['characters_created']} personajes nuevos";
        }
        if ($stats['characters_linked']) {
            $parts[] = "{$stats['characters_linked']} personajes existentes vinculados";
        }
        if ($stats['people_created']) {
            $parts[] = "{$stats['people_created']} personas nuevas";
        }
        if ($stats['people_linked']) {
            $parts[] = "{$stats['people_linked']} personas existentes vinculadas";
        }
        if ($stats['attached']) {
            $parts[] = "{$stats['attached']} agregados al anime";
        }
        if ($stats['voices']) {
            $parts[] = "{$stats['voices']} voces asignadas";
        }

        $message = $parts ? 'Listo: ' . implode(', ', $parts) . '.' : 'No había nada nuevo que agregar.';

        if ($stats['translation_failed']) {
            $message .= ' Las descripciones quedaron en inglés: el traductor no respondió.';
        }

        return $message;
    }
}