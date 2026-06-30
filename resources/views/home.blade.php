<x-layouts.app title="Inicio">
    <div class="space-y-10">

        {{-- Hero --}}
        <section class="hero bg-base-100 rounded-box shadow-sm py-12">
            <div class="hero-content text-center">
                <div class="max-w-md">
                    <h1 class="text-4xl font-bold">Bienvenido a Frikigami</h1>
                    <p class="py-4 text-base-content/70">Noticias, opiniones y catálogo de anime, todo en un solo lugar.</p>
                    <a href="{{ route('animes.index') }}" class="btn btn-primary">Explorar catálogo</a>
                </div>
            </div>
        </section>

        {{-- Últimas noticias --}}
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">Últimas noticias</h2>
                <a href="{{ route('news.index') }}" class="btn btn-ghost btn-sm">Ver todas →</a>
            </div>

            @if ($latestNews->isEmpty())
                <p class="text-base-content/60">No hay noticias publicadas aún.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($latestNews as $news)
                        <a href="{{ route('news.show', $news) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                            @if ($news->cover_image)
                                <figure>
                                    <img src="{{ Storage::url($news->cover_image) }}" alt="{{ $news->title }}" class="h-40 w-full object-cover">
                                </figure>
                            @else
                                <div class="h-40 bg-base-300 flex items-center justify-center text-4xl text-base-content/20">📰</div>
                            @endif
                            <div class="card-body p-4">
                                <h3 class="font-bold text-sm leading-tight">{{ $news->title }}</h3>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($news->animes as $anime)
                                        <span class="badge badge-xs badge-outline">{{ $anime->title }}</span>
                                    @endforeach
                                </div>
                                <p class="text-xs text-base-content/60 mt-1">
                                    {{ $news->user->name ?? 'Desconocido' }} · {{ $news->published_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Animes de temporada --}}
        @if ($currentSeason && $seasonAnimes->isNotEmpty())
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold">Temporada: {{ $currentSeason->label }}</h2>
                    <a href="{{ route('animes.index', ['season' => $currentSeason->id]) }}" class="btn btn-ghost btn-sm">Ver todos →</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3">
                    @foreach ($seasonAnimes as $anime)
                        <a href="{{ route('animes.show', $anime) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                            <figure>
                                @if ($anime->cover_image)
                                    <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="h-36 w-full object-cover">
                                @else
                                    <div class="h-36 w-full bg-base-300 flex items-center justify-center text-3xl text-base-content/20">?</div>
                                @endif
                            </figure>
                            <div class="card-body p-2">
                                <p class="text-xs font-medium leading-tight truncate">{{ $anime->title }}</p>
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
                    <h2 class="text-2xl font-bold">En emisión</h2>
                    <a href="{{ route('animes.index', ['status' => 'ongoing']) }}" class="btn btn-ghost btn-sm">Ver todos →</a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                    @foreach ($ongoingAnimes as $anime)
                        <a href="{{ route('animes.show', $anime) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                            <figure>
                                @if ($anime->cover_image)
                                    <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="h-40 w-full object-cover">
                                @else
                                    <div class="h-40 w-full bg-base-300 flex items-center justify-center text-3xl text-base-content/20">?</div>
                                @endif
                            </figure>
                            <div class="card-body p-2">
                                <p class="text-xs font-medium leading-tight truncate">{{ $anime->title }}</p>
                                @if ($anime->season)
                                    <p class="text-xs text-base-content/60">{{ $anime->season->label }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</x-layouts.app>