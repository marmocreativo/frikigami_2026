<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function index(): View
    {
        $characters = Character::with('animes')
            ->when(request('q'), fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('characters.index', compact('characters'));
    }

    public function show(Character $character): View
    {
        $character->load('animes', 'voiceActors');

        return view('characters.show', compact('character'));
    }
}