<x-anime.layout :anime="$anime" :same-season="$sameSeason" :same-genre="$sameGenre">
    @if ($temporadas->isEmpty())
        <p class="text-base-content/60">No hay temporadas registradas para este anime.</p>
    @else
        <div class="space-y-3">
            @foreach ($temporadas as $temporada)
                <a href="{{ route('animes.temporada', [$anime, $temporada->number]) }}"
                   class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow block">
                    <div class="card-body p-4 flex-row items-center justify-between gap-4">
                        <div>
                            <p class="text-xs text-base-content/60">Temporada {{ $temporada->number }}</p>
                            <h3 class="font-bold text-lg">{{ $temporada->label }}</h3>
                            <p class="text-sm text-base-content/60 mt-1">
                                @if ($temporada->season)
                                    Estreno: {{ $temporada->season->label }}
                                @elseif ($temporada->year)
                                    {{ $temporada->year }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-2xl font-bold">{{ $temporada->episodes_count }}</p>
                            <p class="text-xs text-base-content/60">{{ $temporada->episodes_count === 1 ? 'episodio' : 'episodios' }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-anime.layout>
