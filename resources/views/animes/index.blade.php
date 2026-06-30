<x-layouts.app title="Catálogo de Animes">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Catálogo de Animes</h1>
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="GET" action="{{ route('animes.index') }}" class="bg-base-100 rounded-box p-4 mb-6 flex flex-wrap gap-3 items-end shadow-sm">
        <div class="form-control flex-1 min-w-40">
            <label class="label"><span class="label-text">Buscar</span></label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Título del anime..." class="input input-bordered input-sm">
        </div>

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
    </form>

    @if ($animes->isEmpty())
        <div class="text-center py-16">
            <p class="text-base-content/60 text-lg">No se encontraron animes con esos filtros.</p>
            <a href="{{ route('animes.index') }}" class="btn btn-ghost btn-sm mt-2">Ver todos</a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($animes as $anime)
                <a href="{{ route('animes.show', $anime) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                    <figure>
                        @if ($anime->cover_image)
                            <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="h-48 w-full object-cover">
                        @else
                            <div class="h-48 w-full bg-base-300 flex items-center justify-center text-4xl text-base-content/30">
                                ?
                            </div>
                        @endif
                    </figure>
                    <div class="card-body p-3">
                        <h2 class="card-title text-sm leading-tight">{{ $anime->title }}</h2>
                        @if ($anime->season)
                            <p class="text-xs text-base-content/60">{{ $anime->season->label }}</p>
                        @endif
                        <div class="flex flex-wrap gap-1 mt-1">
                            @php $statusMap = ['upcoming' => 'Próximamente', 'ongoing' => 'En emisión', 'finished' => 'Finalizado']; @endphp
                            <span class="badge badge-xs {{ $anime->status === 'ongoing' ? 'badge-success' : ($anime->status === 'finished' ? 'badge-neutral' : 'badge-warning') }}">
                                {{ $statusMap[$anime->status] }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $animes->links() }}
        </div>
    @endif
</x-layouts.app>