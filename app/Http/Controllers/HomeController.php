<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\News;
use App\Models\Season;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        // Últimas temporadas registradas (para la fila de "categorías")
        $seasons = Season::withCount('animes')
            ->orderByDesc('year')
            ->orderByRaw("FIELD(name, 'Otoño', 'Verano', 'Primavera', 'Invierno')")
            ->take(6)
            ->get();

        // Animes de la temporada actual (la más reciente)
        $currentSeason = Season::orderByDesc('year')
            ->orderByRaw("FIELD(name, 'Otoño', 'Verano', 'Primavera', 'Invierno')")
            ->first();

        $seasonAnimes = Anime::with('genres')
            ->where('season_id', $currentSeason?->id)
            ->latest()
            ->take(8)
            ->get();

        // Últimas noticias publicadas
        $latestNews = News::with('user', 'animes')
            ->published()
            ->latest('published_at')
            ->take(6)
            ->get();

        // Animes en emisión
        $ongoingAnimes = Anime::with('season')
            ->where('status', 'ongoing')
            ->latest()
            ->take(6)
            ->get();

        return view('home', compact('seasons', 'currentSeason', 'seasonAnimes', 'latestNews', 'ongoingAnimes'));
    }
}