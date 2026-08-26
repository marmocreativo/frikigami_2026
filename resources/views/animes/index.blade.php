<x-layouts.app title="Catálogo de Animes">
    <x-container class="pt-28">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Catálogo de Animes</h1>
        </div>

        @if (session('status'))
            <div class="alert alert-success mb-4">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Buscador minimalista + filtros avanzados desplegables --}}
        <form method="GET" action="{{ route('animes.index') }}" class="mb-8">
            <div class="relative w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-1/2 -translate-y-1/2 stroke-current text-base-content/50 pointer-events-none" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar anime..."
                       class="input input-bordered w-full pl-12 pr-4 rounded-full">
            </div>

            <details class="w-full mt-3 group">
                <summary class="cursor-pointer text-sm text-base-content/60 hover:text-primary transition-colors list-none flex items-center justify-center gap-1">
                    Filtros avanzados
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>

                <div class="bg-base-100 rounded-box p-4 mt-3 flex flex-wrap gap-3 items-end shadow-sm">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Género</span></label>
                        <select name="genre" class="select select-bordered select-sm">
                            <option value="">Todos</option>
                            @foreach ($genres as $genre)
                                <option value="{{ $genre->slug }}" @selected(request('genre') === $genre->slug)>{{ $genre->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Temporada</span></label>
                        <select name="season" class="select select-bordered select-sm">
                            <option value="">Todas</option>
                            @foreach ($seasons as $season)
                                <option value="{{ $season->id }}" @selected(request('season') == $season->id)>{{ $season->label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text">Estado</span></label>
                        <select name="status" class="select select-bordered select-sm">
                            <option value="">Todos</option>
                            <option value="upcoming" @selected(request('status') === 'upcoming')>Próximamente</option>
                            <option value="ongoing" @selected(request('status') === 'ongoing')>En emisión</option>
                            <option value="finished" @selected(request('status') === 'finished')>Finalizado</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                    @if (request()->hasAny(['q', 'genre', 'season', 'status']))
                        <a href="{{ route('animes.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
                    @endif
                </div>
            </details>
        </form>

        @if ($animes->isEmpty())
            <div class="text-center py-16">
                <p class="text-base-content/60 text-lg">No se encontraron animes con esos filtros.</p>
                <a href="{{ route('animes.index') }}" class="btn btn-ghost btn-sm mt-2">Ver todos</a>
            </div>
        @else
            @php
                $statusMap = [
                    'upcoming' => 'Próximamente',
                    'ongoing'  => 'En emisión',
                    'finished' => 'Finalizado',
                ];
                $statusBadge = [
                    'upcoming' => 'bg-warning/90 text-warning-content',
                    'ongoing'  => 'bg-success/90 text-success-content',
                    'finished' => 'bg-neutral/90 text-neutral-content',
                ];
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                {{-- Listado de animes: 3/4 --}}
                <div class="lg:col-span-3">
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach ($animes as $anime)
                            <a href="{{ route('animes.show', $anime) }}"
                               class="relative rounded-box overflow-hidden aspect-[3/4] hover:-translate-y-0.5 transition-all">
                                @if ($anime->cover_image)
                                    <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="absolute inset-0 h-full w-full object-cover">
                                @else
                                    <div class="absolute inset-0 bg-base-300 flex items-center justify-center text-3xl text-base-content/30">?</div>
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-t from-neutral/90 via-neutral/20 to-transparent"></div>

                                <span class="badge badge-xs {{ $statusBadge[$anime->status] }} border-none absolute top-3 left-3">
                                    {{ $statusMap[$anime->status] }}
                                </span>

                                <div class="absolute inset-x-0 bottom-0 p-3">
                                    <p class="font-bold text-sm text-neutral-content leading-tight">{{ $anime->title }}</p>
                                    @if ($anime->season)
                                        <p class="text-xs text-neutral-content/70">{{ $anime->season->label }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $animes->links() }}
                    </div>
                </div>

                {{-- Sidebar: 1/4 --}}
                <aside class="lg:col-span-1 space-y-8 bg-base-100 rounded-box p-6 h-fit">
                    <div>
                        <h3 class="font-bold text-sm uppercase tracking-wide text-base-content/60 mb-3">Temporadas</h3>
                        <ul class="space-y-2">
                            @foreach ($seasons as $season)
                                <li>
                                    <a href="{{ route('animes.index', ['season' => $season->id]) }}"
                                       class="text-sm {{ request('season') == $season->id ? 'text-primary font-bold' : 'text-base-content/70 hover:text-primary' }} transition-colors">
                                        {{ $season->label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-bold text-sm uppercase tracking-wide text-base-content/60 mb-3">Géneros</h3>
                        <ul class="space-y-2">
                            @foreach ($genres as $genre)
                                <li>
                                    <a href="{{ route('animes.index', ['genre' => $genre->slug]) }}"
                                       class="text-sm {{ request('genre') === $genre->slug ? 'text-primary font-bold' : 'text-base-content/70 hover:text-primary' }} transition-colors">
                                        {{ $genre->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>

            </div>
        @endif
    </x-container>
</x-layouts.app>