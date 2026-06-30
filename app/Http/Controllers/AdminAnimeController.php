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
    public function index(): View
    {
        $animes = Anime::with('season', 'genres')
            ->latest()
            ->paginate(20);

        return view('admin.animes.index', compact('animes'));
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