<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminPersonController extends Controller
{
    public function index(): View
    {
        $people = Person::orderBy('name')->paginate(20);

        return view('admin.people.index', compact('people'));
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