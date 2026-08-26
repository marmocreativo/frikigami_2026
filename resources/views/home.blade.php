<x-layouts.app title="Inicio">
    <div class="space-y-14">

        {{-- Hero: ancho completo, 720px mínimo --}}
        <section class="relative w-full min-h-[720px] overflow-hidden">
            <img src="{{ asset('images/hero_bg_default.jpg') }}"
                alt="Hero de prueba"
                class="absolute inset-0 h-full w-full object-cover">

            {{-- overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-neutral/10 via-neutral/40 to-transparent"></div>

            <div class="relative z-10 h-full min-h-[720px] flex items-end">
                <div class="w-full max-w-8xl mx-auto px-4 md:px-8">
                    <div class="max-w-2xl pb-16">
                        @if ($latestNews->first())
                            <div class="flex gap-2 mb-3">
                                @foreach ($latestNews->first()->animes->take(2) as $anime)
                                    <span class="badge badge-sm bg-base-100/90 text-base-content border-none p-2">{{ $anime->title }}</span>
                                @endforeach
                            </div>

                            <h1 class="text-4xl md:text-5xl font-bold text-neutral-content leading-tight">
                                {{ $latestNews->first()->title }}
                            </h1>

                            <p class="text-sm text-neutral-content/70 mt-3">
                                {{ $latestNews->first()->user->name ?? 'Desconocido' }} · {{ $latestNews->first()->published_at->format('d/m/Y') }}
                            </p>

                            <a href="{{ route('news.show', $latestNews->first()) }}" class="btn btn-primary mt-6">
                                Leer noticia
                            </a>
                        @else
                            <h1 class="text-4xl md:text-5xl font-bold text-neutral-content">Bienvenido a Frikigami</h1>
                            <p class="text-base text-neutral-content/70 mt-3">Noticias, opiniones y catálogo de anime, todo en un solo lugar.</p>
                            <a href="{{ route('animes.index') }}" class="btn btn-primary mt-6">Explorar catálogo</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Resto del contenido: boxed --}}
        <x-container>
            <div class="space-y-14">

                {{-- Temporadas (estilo "Blog Categories") --}}
                @if ($seasons->isNotEmpty())
                    <x-container>
                        <h2 class="text-3xl font-bold text-base-content mb-6">Temporadas</h2>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                            @php
                                $seasonAccents = ['bg-primary/10', 'bg-secondary/10'];
                            @endphp
                            @foreach ($seasons as $index => $season)
                                <a href="{{ route('animes.index', ['season' => $season->id]) }}"
                                class="card {{ $seasonAccents[$index % count($seasonAccents)] }} border-none hover:-translate-y-0.5 transition-all">
                                    <div class="card-body items-center justify-center text-center p-6">
                                        <p class="text-xl font-bold text-base-content">{{ $season->label }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </x-container>
                @endif

                {{-- Últimas noticias --}}
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-3xl font-bold text-base-content">Últimas noticias</h2>
                        <a href="{{ route('news.index') }}" class="btn btn-ghost btn-sm text-primary">Ver todas →</a>
                    </div>

                    @if ($latestNews->isEmpty())
                        <p class="text-base text-base-content/60">No hay noticias publicadas aún.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                            @foreach ($latestNews as $index => $news)
                                <a href="{{ route('news.show', $news) }}"
                                   class="{{ $index === 0 ? 'sm:col-span-2' : 'sm:col-span-1' }} hover:-translate-y-0.5 transition-all">
                                    <div class="relative rounded-box overflow-hidden">
                                        @if ($news->cover_image)
                                            <img src="{{ Storage::url($news->cover_image) }}" alt="{{ $news->title }}" class="h-72 w-full object-cover">
                                        @else
                                            <div class="h-72 w-full bg-base-300 flex items-center justify-center text-4xl text-base-content/30">📰</div>
                                        @endif

                                        <span class="badge badge-sm bg-base-100/90 text-base-content border-none absolute top-3 left-3 p-2">
                                            RECIENTE
                                        </span>
                                    </div>

                                    <h3 class="font-bold text-[24px] leading-tight text-base-content mt-3">
                                        {{ $news->title }}
                                    </h3>
                                    <p class="text-sm text-base-content/60 mt-1">
                                        {{ $news->user->name ?? 'Desconocido' }} · {{ $news->published_at->format('d/m/Y') }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Animes de temporada --}}
                @if ($currentSeason && $seasonAnimes->isNotEmpty())
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-3xl font-bold text-base-content">Temporada: {{ $currentSeason->label }}</h2>
                            <a href="{{ route('animes.index', ['season' => $currentSeason->id]) }}" class="btn btn-ghost btn-sm text-primary">Ver todos →</a>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3">
                            @foreach ($seasonAnimes as $anime)
                                <a href="{{ route('animes.show', $anime) }}"
                                   class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                                    <figure>
                                        @if ($anime->cover_image)
                                            <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="h-36 w-full object-cover">
                                        @else
                                            <div class="h-36 w-full bg-base-200 flex items-center justify-center text-3xl text-base-content/20">?</div>
                                        @endif
                                    </figure>
                                    <div class="card-body p-2">
                                        <p class="text-sm font-medium leading-tight truncate text-base-content">{{ $anime->title }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Animes en emisión --}}
                @if ($ongoingAnimes->isNotEmpty())
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-3xl font-bold text-base-content">En emisión</h2>
                            <a href="{{ route('animes.index', ['status' => 'ongoing']) }}" class="btn btn-ghost btn-sm text-primary">Ver todos →</a>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach ($ongoingAnimes->take(4) as $anime)
                                <a href="{{ route('animes.show', $anime) }}"
                                   class="relative rounded-box overflow-hidden aspect-[3/4] hover:-translate-y-0.5 transition-all">
                                    @if ($anime->cover_image)
                                        <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="absolute inset-0 h-full w-full object-cover">
                                    @else
                                        <div class="absolute inset-0 bg-base-300 flex items-center justify-center text-3xl text-base-content/30">?</div>
                                    @endif

                                    <div class="absolute inset-0 bg-gradient-to-t from-neutral/90 via-neutral/20 to-transparent"></div>

                                    <span class="badge badge-xs bg-success/90 text-success-content border-none absolute top-3 left-3">
                                        En emisión
                                    </span>

                                    <div class="absolute inset-x-0 bottom-0 p-4">
                                        <p class="font-bold text-neutral-content leading-tight">{{ $anime->title }}</p>
                                        @if ($anime->season)
                                            <p class="text-sm text-neutral-content/70 p-2">{{ $anime->season->label }}</p>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>
        </x-container>

    </div>
</x-layouts.app>