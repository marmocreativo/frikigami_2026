<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCharacterRequest;
use App\Models\Character;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Anime;
use Illuminate\Http\Request;

class AdminCharacterController extends Controller
{
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        $term = $request->query('q');
        $byName = fn ($query) => $query->when($term, fn ($q) => $q->where('characters.name', 'like', "%{$term}%"));

        // Agrupados por anime (un personaje aparece en cada anime donde participa)
        $groups = Anime::with(['season', 'characters' => fn ($query) => $byName($query)->orderBy('characters.name')])
            ->whereHas('characters', $byName)
            ->when($currentAnime, fn ($query, $anime) => $query->where('id', $anime->id))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        // Personajes sin anime: para que nunca queden fuera del listado
        $orphans = (! $currentAnime && ! $groups->hasMorePages())
            ? $byName(Character::doesntHave('animes'))->orderBy('name')->get()
            : collect();

        return view('admin.characters.index', compact('groups', 'orphans', 'animes', 'currentAnime'));
    }

    public function create(): View
    {
        return view('admin.characters.create');
    }

    public function store(StoreCharacterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('characters', 'public');
        }

        Character::create($data);

        return redirect()->route('admin.personajes.index')
            ->with('status', 'Personaje creado correctamente.');
    }

    public function show(Character $character): RedirectResponse
    {
        return redirect()->route('personajes.show', $character);
    }

    public function edit(Character $character): View
    {
        return view('admin.characters.edit', compact('character'));
    }

    public function update(StoreCharacterRequest $request, Character $character): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('characters', 'public');
        }

        $character->update($data);

        return redirect()->route('admin.personajes.index')
            ->with('status', 'Personaje actualizado correctamente.');
    }

    public function destroy(Character $character): RedirectResponse
    {
        $character->delete();

        return redirect()->route('admin.personajes.index')
            ->with('status', 'Personaje eliminado.');
    }
}