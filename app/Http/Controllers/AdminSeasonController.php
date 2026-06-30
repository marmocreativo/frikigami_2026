<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSeasonController extends Controller
{
    public function index(): View
    {
        $seasons = Season::withCount('animes')
            ->orderByDesc('year')
            ->orderByRaw("FIELD(name, 'Otoño', 'Verano', 'Primavera', 'Invierno')")
            ->paginate(20);

        return view('admin.seasons.index', compact('seasons'));
    }

    public function create(): View
    {
        return view('admin.seasons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', Rule::in(['Invierno', 'Primavera', 'Verano', 'Otoño'])],
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:2100',
                Rule::unique('seasons')->where(fn($q) => $q->where('name', $request->name)),
            ],
        ]);

        Season::create($request->only('name', 'year'));

        return redirect()->route('admin.seasons.index')
            ->with('status', 'Temporada creada correctamente.');
    }

    public function show(): RedirectResponse
    {
        return redirect()->route('admin.seasons.index');
    }

    public function edit(Season $season): View
    {
        return view('admin.seasons.edit', compact('season'));
    }

    public function update(Request $request, Season $season): RedirectResponse
    {
        $request->validate([
            'name' => ['required', Rule::in(['Invierno', 'Primavera', 'Verano', 'Otoño'])],
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:2100',
                Rule::unique('seasons')->where(fn($q) => $q->where('name', $request->name))->ignore($season->id),
            ],
        ]);

        $season->update($request->only('name', 'year'));

        return redirect()->route('admin.seasons.index')
            ->with('status', 'Temporada actualizada correctamente.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        $season->delete();

        return redirect()->route('admin.seasons.index')
            ->with('status', 'Temporada eliminada.');
    }
}