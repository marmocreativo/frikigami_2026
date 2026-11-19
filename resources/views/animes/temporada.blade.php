<x-anime.layout :anime="$anime" :same-season="$sameSeason" :same-genre="$sameGenre">
    <h3 class="text-lg font-bold mb-3">{{ $temporada->label }}</h3>

    @if ($temporada->episodes->isEmpty())
        <p class="text-base-content/60 text-sm">Sin episodios registrados.</p>
    @else
        <div class="space-y-3">
            @foreach ($temporada->episodes->sortBy('number') as $episodio)
                <a href="{{ route('animes.episodio', [$anime, $temporada->number, $episodio->number]) }}"
                   class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow block">
                    <div class="card-body p-3 flex-row gap-4 items-center">
                        @if ($episodio->thumbnail_url)
                            <img src="{{ $episodio->thumbnail_url }}" alt="" loading="lazy"
                                 class="w-32 sm:w-40 aspect-video object-cover rounded shrink-0">
                        @else
                            <div class="w-32 sm:w-40 aspect-video rounded bg-base-300 flex items-center justify-center text-2xl text-base-content/30 shrink-0">
                                {{ $episodio->number }}
                            </div>
                        @endif

                        <div class="min-w-0">
                            <p class="text-xs text-base-content/60">
                                Episodio {{ $episodio->number }}@if ($episodio->air_date) · {{ $episodio->air_date->format('d/m/Y') }}@endif
                            </p>
                            <p class="font-bold leading-tight">{{ $episodio->title ?: 'Episodio '.$episodio->number }}</p>
                            @if ($episodio->synopsis)
                                <p class="text-sm text-base-content/60 line-clamp-2 mt-1">{{ $episodio->synopsis }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <a href="{{ route('animes.episodios', $anime) }}" class="link link-hover text-sm mt-4 inline-block">← Todas las temporadas</a>
</x-anime.layout>
