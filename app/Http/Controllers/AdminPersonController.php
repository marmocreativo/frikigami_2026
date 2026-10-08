<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Anime;
use Illuminate\Http\Request;

class AdminPersonController extends Controller
{
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        $people = Person::query()
            ->when($currentAnime, fn ($query, $anime) => $query
                // Staff (anime_person) o actor de voz (character_person) de este anime
                ->where(fn ($q) => $q
                    ->whereHas('animes', fn ($a) => $a->where('animes.id', $anime->id))
                    ->orWhereHas('characters', fn ($c) => $c->where('character_person.anime_id', $anime->id))
                )
                // Solo el rol y los personajes que corresponden a este anime
                ->with([
                    'animes' => fn ($a) => $a->where('animes.id', $anime->id),
                    'characters' => fn ($c) => $c->wherePivot('anime_id', $anime->id),
                ])
            )
            ->when($request->query('q'), fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.people.index', compact('people', 'animes', 'currentAnime'));
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

        return redirect()->route('admin.people.index')
            ->with('status', 'Persona creada correctamente.');
    }

    public function show(Person $person): RedirectResponse
    {
        return redirect()->route('people.show', $person);
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

        return redirect()->route('admin.people.index')
            ->with('status', 'Persona actualizada correctamente.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $person->delete();

        return redirect()->route('admin.people.index')
            ->with('status', 'Persona eliminada.');
    }
}