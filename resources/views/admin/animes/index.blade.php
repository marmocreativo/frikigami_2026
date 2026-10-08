<x-layouts.admin title="Animes">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Animes</h1>
        <a href="{{ route('admin.animes.create') }}" class="btn btn-primary btn-sm">+ Nuevo Anime</a>
    </div>

    @include('admin.partials.alert')

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.animes.index') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row flex-wrap items-center gap-3">
            <label class="input input-sm w-full sm:w-64">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por título..." />
            </label>

            <select name="season" class="select select-sm w-full sm:w-44" onchange="this.form.submit()">
                <option value="">Todas las temporadas</option>
                @foreach ($seasons as $season)
                    <option value="{{ $season->id }}" @selected(request('season') == $season->id)>{{ $season->label }}</option>
                @endforeach
            </select>

            <select name="genre" class="select select-sm w-full sm:w-44" onchange="this.form.submit()">
                <option value="">Todos los géneros</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre->slug }}" @selected(request('genre') === $genre->slug)>{{ $genre->name }}</option>
                @endforeach
            </select>

            <select name="status" class="select select-sm w-full sm:w-44" onchange="this.form.submit()">
                <option value="">Cualquier estado</option>
                <option value="upcoming" @selected(request('status') === 'upcoming')>Próximamente</option>
                <option value="ongoing" @selected(request('status') === 'ongoing')>En emisión</option>
                <option value="finished" @selected(request('status') === 'finished')>Finalizado</option>
            </select>

            <select name="sort" class="select select-sm w-full sm:w-52" onchange="this.form.submit()">
                @foreach ($sortLabels as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>

            @if (request()->anyFilled(['q', 'season', 'genre', 'status', 'sort']))
                <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Temporada</th>
                        <th>Estado</th>
                        <th>Géneros</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $statusMap = ['upcoming' => 'Próximamente', 'ongoing' => 'En emisión', 'finished' => 'Finalizado']; @endphp

                    @forelse ($animes as $anime)
                        <tr>
                            <td class="font-medium">{{ $anime->title }}</td>
                            <td>{{ $anime->season?->label ?? '—' }}</td>
                            <td>
                                <span class="badge badge-sm {{ $anime->status === 'ongoing' ? 'badge-success' : ($anime->status === 'finished' ? 'badge-neutral' : 'badge-warning') }}">
                                    {{ $statusMap[$anime->status] }}
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($anime->genres as $genre)
                                        <span class="badge badge-xs badge-outline">{{ $genre->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    {{-- Contenido relacionado del anime --}}
                                    <button type="button" class="btn btn-ghost btn-xs"
                                            popovertarget="rel-{{ $anime->id }}"
                                            style="anchor-name:--rel-{{ $anime->id }}">
                                        Contenido
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    <ul id="rel-{{ $anime->id }}" popover
                                        class="dropdown dropdown-end menu menu-sm w-56 rounded-box bg-base-100 shadow-lg"
                                        style="position-anchor:--rel-{{ $anime->id }}">
                                        <li><a href="{{ route('admin.anime-seasons.index', ['anime' => $anime->slug]) }}">Temporadas del anime</a></li>
                                        <li><a href="{{ route('admin.episodes.index', ['anime' => $anime->slug]) }}">Episodios del anime</a></li>
                                        <li><a href="{{ route('admin.characters.index', ['anime' => $anime->slug]) }}">Personajes del anime</a></li>
                                        <li><a href="{{ route('admin.people.index', ['anime' => $anime->slug]) }}">Personas (cast) del anime</a></li>
                                        <li><a href="{{ route('admin.comments.index', ['anime' => $anime->slug]) }}">Comentarios del anime</a></li>
                                    </ul>

                                    <a href="{{ route('animes.show', $anime) }}" class="btn btn-ghost btn-xs" target="_blank">Ver</a>
                                    <a href="{{ route('admin.animes.show', $anime) }}" class="btn btn-outline btn-xs">Gestionar</a>
                                    <a href="{{ route('admin.animes.edit', $anime) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.animes.destroy', $anime) }}" onsubmit="return confirm('¿Eliminar este anime?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60 py-8">No se encontraron animes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $animes->links() }}</div>
</x-layouts.admin>