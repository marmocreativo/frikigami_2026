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
}
