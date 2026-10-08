<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnimeRequest;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Season;
use App\Models\Character;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAnimeController extends Controller
{
    public function index(Request $request): View
    {
        $sortOptions = [
            'recent'     => ['label' => 'Más recientes',          'column' => 'created_at', 'direction' => 'desc'],
            'oldest'     => ['label' => 'Más antiguos',           'column' => 'created_at', 'direction' => 'asc'],
            'updated'    => ['label' => 'Editados recientemente', 'column' => 'updated_at', 'direction' => 'desc'],
            'title_asc'  => ['label' => 'Título (A-Z)',           'column' => 'title',      'direction' => 'asc'],
            'title_desc' => ['label' => 'Título (Z-A)',           'column' => 'title',      'direction' => 'desc'],
        ];

        $sort = in_array($request->query('sort'), array_keys($sortOptions), true)
            ? $request->query('sort')
            : 'recent';

        $animes = Anime::with('season', 'genres')
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->when($request->query('season'), fn ($query, $id) => $query->where('season_id', $id))
            ->when($request->query('genre'), fn ($query, $slug) => $query->whereHas('genres', fn ($g) => $g->where('slug', $slug)))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderBy($sortOptions[$sort]['column'], $sortOptions[$sort]['direction'])
            ->paginate(20)
            ->withQueryString();

        $genres = Genre::orderBy('name')->get();
        $seasons = Season::orderByDesc('year')->orderBy('name')->get();
        $sortLabels = collect($sortOptions)->map(fn ($option) => $option['label'])->all();

        return view('admin.animes.index', compact('animes', 'genres', 'seasons', 'sort', 'sortLabels'));
    }

    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();
        $seasons = Season::orderByDesc('year')->orderBy('name')->get();

        return view('admin.animes.create', compact('genres', 'seasons'));
    }

    public function store(StoreAnimeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('animes', 'public');
        }

        $anime = Anime::create($data);
        $anime->genres()->sync($data['genres'] ?? []);

        return redirect()->route('admin.animes.index')
            ->with('status', 'Anime creado correctamente.');
    }

    public function show(Anime $anime): View
    {
        $anime->load('genres', 'season', 'animeSeasons', 'characters.voiceActors', 'staff');

        $tab = request('tab', 'cast');

        $availableCharacters = Character::orderBy('name')->get();
        $availablePeople = Person::orderBy('name')->get();

        return view('admin.animes.show', compact('anime', 'tab', 'availableCharacters', 'availablePeople'));
    }

    public function edit(Anime $anime): View
    {
        $genres = Genre::orderBy('name')->get();
        $seasons = Season::orderByDesc('year')->orderBy('name')->get();
        $anime->load('genres');

        return view('admin.animes.edit', compact('anime', 'genres', 'seasons'));
    }

    public function update(StoreAnimeRequest $request, Anime $anime): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('animes', 'public');
        }

        $anime->update($data);
        $anime->genres()->sync($data['genres'] ?? []);

        return redirect()->route('admin.animes.index')
            ->with('status', 'Anime actualizado correctamente.');
    }

    public function destroy(Anime $anime): RedirectResponse
    {
        $anime->delete();

        return redirect()->route('admin.animes.index')
            ->with('status', 'Anime eliminado.');
    }

    public function attachStaff(Request $request, Anime $anime): RedirectResponse
    {
        $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'role'      => ['required', 'string', 'max:255'],
        ]);

        $anime->staff()->syncWithoutDetaching([
            $request->person_id => ['role' => $request->role]
        ]);

        return redirect()->route('admin.animes.show', ['anime' => $anime, 'tab' => 'cast'])
            ->with('status', 'Staff agregado correctamente.');
    }

    public function detachStaff(Anime $anime, Person $person): RedirectResponse
    {
        $anime->staff()->detach($person->id);

        return redirect()->route('admin.animes.show', ['anime' => $anime, 'tab' => 'cast'])
            ->with('status', 'Staff eliminado.');
    }

    public function attachCharacter(Request $request, Anime $anime): RedirectResponse
    {
        $request->validate([
            'character_id' => ['required', 'exists:characters,id'],
            'person_id'    => ['nullable', 'exists:people,id'],
        ]);

        $anime->characters()->syncWithoutDetaching([$request->character_id]);

        if ($request->person_id) {
            \DB::table('character_person')->updateOrInsert(
                [
                    'character_id' => $request->character_id,
                    'person_id'    => $request->person_id,
                    'anime_id'     => $anime->id,
                ],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('admin.animes.show', ['anime' => $anime, 'tab' => 'characters'])
            ->with('status', 'Personaje agregado correctamente.');
    }

    public function detachCharacter(Anime $anime, Character $character): RedirectResponse
    {
        $anime->characters()->detach($character->id);

        \DB::table('character_person')
            ->where('character_id', $character->id)
            ->where('anime_id', $anime->id)
            ->delete();

        return redirect()->route('admin.animes.show', ['anime' => $anime, 'tab' => 'characters'])
            ->with('status', 'Personaje eliminado.');
    }

    public function attachVoiceActor(Request $request, Anime $anime, Character $character): RedirectResponse
    {
        $request->validate([
            'person_id' => ['required', 'exists:people,id'],
        ]);

        \DB::table('character_person')->updateOrInsert(
            [
                'character_id' => $character->id,
                'person_id'    => $request->person_id,
                'anime_id'     => $anime->id,
            ],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return redirect()->route('admin.animes.show', ['anime' => $anime, 'tab' => 'characters'])
            ->with('status', 'Actor de voz vinculado.');
    }

    public function detachVoiceActor(Anime $anime, Character $character, Person $person): RedirectResponse
    {
        \DB::table('character_person')
            ->where('character_id', $character->id)
            ->where('person_id', $person->id)
            ->where('anime_id', $anime->id)
            ->delete();

        return redirect()->route('admin.animes.show', ['anime' => $anime, 'tab' => 'characters'])
            ->with('status', 'Actor de voz desvinculado.');
    }
}