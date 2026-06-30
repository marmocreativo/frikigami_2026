<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminGenreController extends Controller
{
    public function index(): View
    {
        $genres = Genre::withCount('animes')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.genres.index', compact('genres'));
    }

    public function create(): View
    {
        return view('admin.genres.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('genres', 'name')],
        ]);

        $data['slug'] = Str::slug($data['name']);

        Genre::create($data);

        return redirect()->route('admin.genres.index')
            ->with('status', 'Género creado correctamente.');
    }

    public function show(): RedirectResponse
    {
        return redirect()->route('admin.genres.index');
    }

    public function edit(Genre $genre): View
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('genres', 'name')->ignore($genre->id)],
        ]);

        $data['slug'] = Str::slug($data['name']);

        $genre->update($data);

        return redirect()->route('admin.genres.index')
            ->with('status', 'Género actualizado correctamente.');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        $genre->delete();

        return redirect()->route('admin.genres.index')
            ->with('status', 'Género eliminado.');
    }
}