@props(['anime', 'sameSeason', 'sameGenre'])
<x-layouts.app :title="$anime->title">
    <x-container class="pt-28">
        @if (session('status'))
            <div class="alert alert-success mb-4">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            {{-- Contenido principal: 3/4 --}}
            <div class="lg:col-span-3">

                {{-- Imagen + sinopsis --}}
                <div class="flex flex-col md:flex-row gap-6 mb-6">
                    <div class="w-full md:w-64 shrink-0">
                        @if ($anime->cover_image)
                            <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="w-full aspect-[3/4] object-cover rounded-box shadow">
                        @else
                            <div class="w-full aspect-[3/4] bg-base-300 rounded-box shadow flex items-center justify-center text-4xl text-base-content/30">
                                ?
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <h1 class="text-3xl font-bold">{{ $anime->title }}</h1>

                        @php $ratingCount = $anime->ratings()->count(); @endphp
                        <a href="{{ route('animes.show', $anime) }}#calificaciones" class="inline-flex items-center gap-2 mt-1 group">
                            <x-stars :value="$anime->displayRating()" size="text-xl" />
                            <span class="text-sm text-base-content/60 group-hover:text-primary transition-colors">
                                {{ number_format($anime->displayRating(), 1) }}
                                · {{ $ratingCount ? $ratingCount.' '.($ratingCount === 1 ? 'calificación' : 'calificaciones') : 'Sin calificaciones' }}
                            </span>
                        </a>

                        <div class="flex flex-wrap gap-2 my-3">
                            <span class="badge badge-primary">{{ ucfirst($anime->status) }}</span>
                            @if ($anime->season)
                                <span class="badge badge-outline">{{ $anime->season->label }}</span>
                            @endif
                            @foreach ($anime->genres as $genre)
                                <span class="badge badge-outline">{{ $genre->name }}</span>
                            @endforeach
                        </div>

                        @if ($anime->synopsis)
                            <p class="text-base text-base-content/70">{{ $anime->synopsis }}</p>
                        @endif
                    </div>
                </div>

                @php
                    $tabs = [
                        'animes.show' => 'Información',
                        'animes.temporadas' => 'Temporadas',
                        'animes.reparto' => 'Reparto',
                        'animes.personajes' => 'Personajes',
                        'animes.episodios' => 'Episodios',
                        'animes.noticias' => 'Noticias',
                    ];
                @endphp

                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach ($tabs as $routeName => $label)
                        <a href="{{ route($routeName, $anime) }}"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
                                {{ (request()->routeIs($routeName) || ($routeName === 'animes.episodios' && request()->routeIs('animes.temporada', 'animes.episodio')))
                                        ? 'bg-primary text-primary-content'
                                        : 'bg-base-100 text-base-content/60 hover:text-base-content' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div>
                    {{ $slot }}
                </div>
            </div>

            {{-- Sidebar: 1/4 --}}
            <aside class="lg:col-span-1 space-y-8 bg-base-100 rounded-box p-6 h-fit">

                @if ($anime->season && $sameSeason->isNotEmpty())
                    <div>
                        <h3 class="font-bold text-sm uppercase tracking-wide text-base-content/60 mb-3">
                            Misma temporada
                        </h3>
                        <ul class="space-y-3">
                            @foreach ($sameSeason as $related)
                                <li>
                                    <a href="{{ route('animes.show', $related) }}" class="flex items-center gap-3 group">
                                        <div class="w-12 aspect-[3/4] rounded-field overflow-hidden shrink-0">
                                            @if ($related->cover_image)
                                                <img src="{{ Storage::url($related->cover_image) }}" alt="{{ $related->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-base-300 flex items-center justify-center text-sm text-base-content/30">?</div>
                                            @endif
                                        </div>
                                        <span class="text-sm text-base-content/80 group-hover:text-primary transition-colors leading-tight">
                                            {{ $related->title }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($sameGenre->isNotEmpty())
                    <div>
                        <h3 class="font-bold text-sm uppercase tracking-wide text-base-content/60 mb-3">
                            Mismo género
                        </h3>
                        <ul class="space-y-3">
                            @foreach ($sameGenre as $related)
                                <li>
                                    <a href="{{ route('animes.show', $related) }}" class="flex items-center gap-3 group">
                                        <div class="w-12 aspect-[3/4] rounded-field overflow-hidden shrink-0">
                                            @if ($related->cover_image)
                                                <img src="{{ Storage::url($related->cover_image) }}" alt="{{ $related->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-base-300 flex items-center justify-center text-sm text-base-content/30">?</div>
                                            @endif
                                        </div>
                                        <span class="text-sm text-base-content/80 group-hover:text-primary transition-colors leading-tight">
                                            {{ $related->title }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </aside>

        </div>
    </x-container>
</x-layouts.app>