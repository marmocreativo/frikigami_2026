<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPersonController extends Controller
{
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        $term = $request->query('q');
        $byName = fn ($query) => $query->when($term, fn ($q) => $q->where('people.name', 'like', "%{$term}%"));

        // Agrupadas por anime: staff (anime_person) y actores de voz (character_person)
        $groups = Anime::with('season')
            ->where(fn ($query) => $query
                ->whereHas('staff', $byName)
                ->orWhereHas('voiceActors', $byName))
            ->when($currentAnime, fn ($query, $anime) => $query->where('id', $anime->id))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        $animeIds = $groups->pluck('id');

        $staffRows = DB::table('anime_person')->whereIn('anime_id', $animeIds)
            ->get(['anime_id', 'person_id', 'role']);
        $voiceRows = DB::table('character_person')
            ->join('characters', 'characters.id', '=', 'character_person.character_id')
            ->whereIn('character_person.anime_id', $animeIds)
            ->get(['character_person.anime_id', 'character_person.person_id', 'characters.name as character']);

        $peopleById = $byName(Person::query())
            ->whereIn('id', $staffRows->pluck('person_id')->merge($voiceRows->pluck('person_id'))->unique())
            ->get()->keyBy('id');

        // entries[anime_id] = [person_id => ['person', 'roles', 'characters']]
        $entries = [];
        foreach ($staffRows as $row) {
            if ($person = $peopleById->get($row->person_id)) {
                $entries[$row->anime_id][$row->person_id]['person'] = $person;
                $entries[$row->anime_id][$row->person_id]['roles'][] = $row->role ?: 'Staff';
            }
        }
        foreach ($voiceRows as $row) {
            if ($person = $peopleById->get($row->person_id)) {
                $entries[$row->anime_id][$row->person_id]['person'] = $person;
                $entries[$row->anime_id][$row->person_id]['characters'][] = $row->character;
            }
        }
        $entries = collect($entries)->map(fn ($list) => collect($list)->sortBy(fn ($e) => $e['person']->name));

        // Personas sin anime: para que nunca queden fuera del listado
        $orphans = (! $currentAnime && ! $groups->hasMorePages())
            ? $byName(Person::doesntHave('animes')->doesntHave('characters'))->orderBy('name')->get()
            : collect();

        return view('admin.people.index', compact('groups', 'entries', 'orphans', 'animes', 'currentAnime'));
    }

    public function create(): View
    {
        return view('admin.people.create');
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('people', 'public');
        }

        Person::create($data);

        return redirect()->route('admin.personas.index')
            ->with('status', 'Persona creada correctamente.');
    }

    public function show(Person $person): RedirectResponse
    {
        return redirect()->route('personas.show', $person);
    }

    public function edit(Person $person): View
    {
        return view('admin.people.edit', compact('person'));
    }

    public function update(StorePersonRequest $request, Person $person): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('people', 'public');
        }

        $person->update($data);

        return redirect()->route('admin.personas.index')
            ->with('status', 'Persona actualizada correctamente.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $person->delete();

        return redirect()->route('admin.personas.index')
            ->with('status', 'Persona eliminada.');
    }
}