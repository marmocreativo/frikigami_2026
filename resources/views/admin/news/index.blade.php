<x-layouts.admin title="Noticias">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Animes</a>
            @endif
            <h1 class="text-2xl font-bold">
                {{ $currentAnime ? 'Noticias de ' . $currentAnime->title : 'Noticias' }}
            </h1>
        </div>
        <a href="{{ route('admin.noticias.create', array_filter(['anime' => $currentAnime?->slug])) }}"
           class="btn btn-primary btn-sm">
            {{ $currentAnime ? '+ Nueva noticia de este anime' : '+ Nueva Noticia' }}
        </a>
    </div>

    @include('admin.partials.alert')

    <form method="GET" action="{{ route('admin.noticias.index') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row flex-wrap items-center gap-3">
            <label class="input input-sm w-full sm:w-64">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por título..." />
            </label>

            <x-admin.anime-filter :animes="$animes" :current="$currentAnime?->slug" />

            <select name="status" class="select select-sm w-full sm:w-40" onchange="this.form.submit()">
                <option value="">Cualquier estado</option>
                <option value="published" @selected($status === 'published')>Publicadas</option>
                <option value="scheduled" @selected($status === 'scheduled')>Programadas</option>
                <option value="draft" @selected($status === 'draft')>Borradores</option>
            </select>

            <select name="sort" class="select select-sm w-full sm:w-56" onchange="this.form.submit()">
                @foreach ($sortLabels as $key => $label)
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>

            @if (request()->anyFilled(['q', 'anime', 'status', 'sort']))
                <a href="{{ route('admin.noticias.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            @endif
        </div>
    </form>
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th class="w-20"></th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Publicada</th>
                        <th>Animes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($news as $item)
                        <tr>
                            <td>
                                @if ($item->cover_image)
                                    <img src="{{ Storage::url($item->cover_image) }}" alt="{{ $item->title }}" loading="lazy"
                                         class="w-16 h-10 object-cover rounded">
                                @else
                                    <div class="w-16 h-10 rounded bg-base-300 flex items-center justify-center text-base-content/30">?</div>
                                @endif
                            </td>
                            <td class="font-medium">{{ $item->title }}</td>
                            <td>{{ $item->user->name ?? '—' }}</td>
                            <td>
                                @if ($item->published_at)
                                    <span class="badge badge-sm badge-success">{{ $item->published_at->format('d/m/Y') }}</span>
                                @else
                                    <span class="badge badge-sm badge-warning">Borrador</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($item->animes as $anime)
                                        <span class="badge badge-xs badge-outline">{{ $anime->title }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    @if ($item->published_at)
                                        <a href="{{ route('noticias.show', $item) }}" class="btn btn-ghost btn-xs" target="_blank">Ver</a>
                                    @endif
                                    <a href="{{ route('admin.noticias.edit', $item) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.noticias.destroy', $item) }}" onsubmit="return confirm('¿Eliminar esta noticia?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-base-content/60 py-8">No hay noticias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $news->links() }}</div>
</x-layouts.admin>