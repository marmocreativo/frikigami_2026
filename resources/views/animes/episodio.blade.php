<x-anime.layout :anime="$anime" :same-season="$sameSeason" :same-genre="$sameGenre">
    <p class="text-sm text-base-content/60">{{ $temporada->label }} · Episodio {{ $episodio->number }}</p>
    <h3 class="text-xl font-bold mb-4">{{ $episodio->title ?: 'Episodio '.$episodio->number }}</h3>

    <div class="flex flex-col md:flex-row gap-6 mb-4">
        @if ($episodio->thumbnail_url)
            <img src="{{ $episodio->thumbnail_url }}" alt="{{ $episodio->title ?: 'Episodio '.$episodio->number }}"
                 class="w-full md:w-96 aspect-video object-cover rounded-box shadow shrink-0">
        @endif

        <div class="flex-1">
            @if ($episodio->air_date)
                <p class="text-sm text-base-content/60 mb-3">Emitido el {{ $episodio->air_date->format('d/m/Y') }}</p>
            @endif

            <h4 class="font-bold mb-1">Resumen</h4>
            @if ($episodio->synopsis)
                <p class="leading-relaxed text-base-content/80">{{ $episodio->synopsis }}</p>
            @else
                <p class="text-base-content/60">Sin resumen todavía.</p>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2 mb-6">
        <x-stars :value="$episodio->displayRating()" size="text-xl" />
        <a href="#calificaciones" class="text-sm text-base-content/60 hover:text-primary transition-colors">
            {{ number_format($episodio->displayRating(), 1) }}
        </a>
    </div>

    <x-review-section :item="$episodio" />

    <a href="{{ route('animes.temporada', [$anime, $temporada->number]) }}" class="link link-hover text-sm mt-6 inline-block">← {{ $temporada->label }}</a>
</x-anime.layout>
