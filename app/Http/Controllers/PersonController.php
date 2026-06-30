<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PersonController extends Controller
{
    public function index(): View
    {
        $people = Person::orderBy('name')->paginate(20);

        return view('people.index', compact('people'));
    }

    public function show(Person $person): View
    {
        $person->load('characters', 'animes');

        return view('people.show', compact('person'));
    }

    public function create(): View
    {
        return view('people.create');
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('people', 'public');
        }

        $person = Person::create($data);

        return redirect()->route('people.show', $person)
            ->with('status', 'Persona creada correctamente.');
    }

    public function edit(Person $person): View
    {
        return view('people.edit', compact('person'));
    }

    public function update(StorePersonRequest $request, Person $person): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('people', 'public');
        }

        $person->update($data);

        return redirect()->route('people.show', $person)
            ->with('status', 'Persona actualizada correctamente.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $person->delete();

        return redirect()->route('people.index')
            ->with('status', 'Persona eliminada.');
    }
}