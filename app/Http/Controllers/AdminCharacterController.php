<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCharacterRequest;
use App\Models\Character;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminCharacterController extends Controller
{
    public function index(): View
    {
        $characters = Character::with('animes')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.characters.index', compact('characters'));
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

        return redirect()->route('admin.characters.index')
            ->with('status', 'Personaje creado correctamente.');
    }

    public function show(Character $character): RedirectResponse
    {
        return redirect()->route('characters.show', $character);
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

        return redirect()->route('admin.characters.index')
            ->with('status', 'Personaje actualizado correctamente.');
    }

    public function destroy(Character $character): RedirectResponse
    {
        $character->delete();

        return redirect()->route('admin.characters.index')
            ->with('status', 'Personaje eliminado.');
    }
}