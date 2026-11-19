{{-- La sinopsis ya se muestra arriba, en la cabecera del anime: aquí no se repite. --}}
@php
    $statusMap = ['upcoming' => 'Próximamente', 'ongoing' => 'En emisión', 'finished' => 'Finalizado'];
@endphp

<div class="space-y-10">

    {{-- Datos generales --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <p class="text-sm text-base-content/60">Estado</p>
            <p class="font-medium">{{ $statusMap[$anime->status] ?? '—' }}</p>
        </div>

        @if ($anime->season)
            <div>
                <p class="text-sm text-base-content/60">Estreno</p>
                <a href="{{ route('animes.estreno', $anime->season->slug) }}" class="font-medium hover:text-primary">{{ $anime->season->label }}</a>
            </div>
        @endif

        <div>
            <p class="text-sm text-base-content/60">Temporadas</p>
            <p class="font-medium">{{ $anime->anime_seasons_count }}</p>
        </div>

        <div>
            <p class="text-sm text-base-content/60">Episodios</p>
            <p class="font-medium">{{ $anime->episodes_count }}</p>
        </div>

        @if ($anime->genres->isNotEmpty())
            <div class="col-span-2 sm:col-span-4">
                <p class="text-sm text-base-content/60 mb-1">Géneros</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($anime->genres as $genre)
                        <a href="{{ route('animes.genero', $genre->slug) }}" class="badge badge-outline hover:badge-primary transition-colors">
                            {{ $genre->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Temporadas --}}
    @if ($temporadas->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold">Temporadas</h3>
                @if ($temporadas->count() > 1)
                    <a href="{{ route('animes.temporadas', $anime) }}" class="btn btn-ghost btn-sm text-primary">Ver todas →</a>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($temporadas->take(4) as $temporada)
                    <a href="{{ route('animes.temporada', [$anime, $temporada->number]) }}"
                       class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="card-body p-4">
                            <p class="font-bold">{{ $temporada->label }}</p>
                            <p class="text-sm text-base-content/60">
                                {{ $temporada->season?->label ?? $temporada->year }}
                                @if ($temporada->season || $temporada->year) · @endif
                                {{ $temporada->episodes_count }} {{ $temporada->episodes_count === 1 ? 'episodio' : 'episodios' }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Reparto (staff) --}}
    @if ($staffSample->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold">Reparto</h3>
                <a href="{{ route('animes.reparto', $anime) }}" class="btn btn-ghost btn-sm text-primary">
                    Ver todo{{ $anime->staff_count > $staffSample->count() ? ' ('.$anime->staff_count.')' : '' }} →
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach ($staffSample as $person)
                    <a href="{{ route('personas.show', $person) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="card-body p-3 flex flex-row items-center gap-3">
                            @if ($person->photo)
                                <img src="{{ Storage::url($person->photo) }}" alt="{{ $person->name }}" class="w-12 h-12 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-neutral text-neutral-content flex items-center justify-center text-lg shrink-0">
                                    {{ strtoupper(substr($person->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-sm truncate">{{ $person->name }}</p>
                                <p class="text-xs text-base-content/60 truncate">{{ $person->pivot->role }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Personajes --}}
    @if ($charactersSample->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold">Personajes</h3>
                <a href="{{ route('animes.personajes', $anime) }}" class="btn btn-ghost btn-sm text-primary">
                    Ver todos{{ $anime->characters_count > $charactersSample->count() ? ' ('.$anime->characters_count.')' : '' }} →
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                @foreach ($charactersSample as $character)
                    <a href="{{ route('personajes.show', $character) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                        <figure class="pt-4">
                            @if ($character->photo)
                                <img src="{{ Storage::url($character->photo) }}" alt="{{ $character->name }}" class="w-16 h-16 rounded-full object-cover">
                            @else
                                <div class="w-16 h-16 rounded-full bg-base-300 flex items-center justify-center text-xl text-base-content/40">
                                    {{ strtoupper(substr($character->name, 0, 1)) }}
                                </div>
                            @endif
                        </figure>
                        <div class="card-body items-center text-center p-3">
                            <p class="font-medium text-sm leading-tight">{{ $character->name }}</p>
                            @if ($character->voiceActors->isNotEmpty())
                                <p class="text-xs text-base-content/60">{{ $character->voiceActors->first()->name }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Episodios --}}
    @if ($episodesSample->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold">Últimos episodios</h3>
                <a href="{{ route('animes.episodios', $anime) }}" class="btn btn-ghost btn-sm text-primary">
                    Ver todos{{ $anime->episodes_count > $episodesSample->count() ? ' ('.$anime->episodes_count.')' : '' }} →
                </a>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <ul class="divide-y divide-base-200">
                    @foreach ($episodesSample as $episode)
                        <li>
                            <a href="{{ route('animes.episodio', [$anime, $episode->animeSeason->number, $episode->number]) }}"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-base-200/50 transition-colors">
                                @if ($episode->thumbnail_url)
                                    <img src="{{ $episode->thumbnail_url }}" alt="" loading="lazy" class="w-20 aspect-video object-cover rounded shrink-0">
                                @endif
                                <span class="text-sm flex-1 min-w-0">
                                    <span class="text-base-content/60">T{{ $episode->animeSeason->number }} · Ep. {{ $episode->number }}</span>
                                    — {{ $episode->title ?: 'Episodio '.$episode->number }}
                                </span>
                                <span class="text-xs text-base-content/60 shrink-0">{{ $episode->air_date?->format('d/m/Y') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Noticias --}}
    @if ($newsSample->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold">Noticias</h3>
                <a href="{{ route('animes.noticias', $anime) }}" class="btn btn-ghost btn-sm text-primary">
                    Ver todas{{ $newsCount > $newsSample->count() ? ' ('.$newsCount.')' : '' }} →
                </a>
            </div>
            <div class="space-y-3">
                @foreach ($newsSample as $item)
                    <a href="{{ route('noticias.show', $item) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow block">
                        <div class="card-body p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="font-bold">{{ $item->title }}</h4>
                                    <p class="text-sm text-base-content/60 mt-1">
                                        Por {{ $item->user->name ?? 'Desconocido' }} · {{ $item->published_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                @if ($item->cover_image)
                                    <img src="{{ Storage::url($item->cover_image) }}" alt="{{ $item->title }}" class="w-20 h-16 object-cover rounded shrink-0">
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Calificación y comentarios --}}
    <x-review-section :item="$anime" />
</div>
