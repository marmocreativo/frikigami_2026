<x-layouts.admin title="Temporadas de serie">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Animes</a>
            @endif
            <h1 class="text-2xl font-bold">
                {{ $currentAnime ? 'Temporadas de ' . $currentAnime->title : 'Temporadas de serie' }}
            </h1>
        </div>

        <div class="flex gap-2">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.anilist', $currentAnime) }}" class="btn btn-outline btn-sm">Buscar en AniList</a>
            @endif
            <a href="{{ route('admin.temporadas.create', array_filter(['anime' => $currentAnime?->slug])) }}"
               class="btn btn-primary btn-sm">+ Nueva Temporada</a>
        </div>
    </div>

    @include('admin.partials.alert')

    <form method="GET" action="{{ route('admin.temporadas.index') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row flex-wrap items-center gap-3">
            <x-admin.anime-filter :animes="$animes" :current="$currentAnime?->slug" />

            @if ($currentAnime)
                <a href="{{ route('admin.temporadas.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            @endif
        </div>
    </form>

    @forelse ($groups as $anime)
        <x-admin.anime-group :anime="$anime">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-semibold">{{ $anime->animeSeasons->count() }} {{ $anime->animeSeasons->count() === 1 ? 'temporada' : 'temporadas' }}</h2>
                <a href="{{ route('admin.temporadas.create', ['anime' => $anime->slug]) }}" class="btn btn-ghost btn-xs">+ Temporada</a>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Estreno</th>
                            <th>Año</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($anime->animeSeasons as $animeSeason)
                            <tr>
                                <td>{{ $animeSeason->number }}</td>
                                <td>{{ $animeSeason->title ?: '—' }}</td>
                                <td>{{ $animeSeason->season?->label ?? '—' }}</td>
                                <td>{{ $animeSeason->year ?? '—' }}</td>
                                <td>
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('admin.episodios.index', ['anime' => $anime->slug, 'anime_season' => $animeSeason->id]) }}"
                                           class="btn btn-ghost btn-xs">
                                            Episodios ({{ $animeSeason->episodes_count }})
                                        </a>
                                        <a href="{{ route('admin.temporadas.edit', $animeSeason) }}" class="btn btn-outline btn-xs">Editar</a>
                                        <form method="POST" action="{{ route('admin.temporadas.destroy', $animeSeason) }}" onsubmit="return confirm('¿Eliminar esta temporada? Se eliminarán también sus episodios.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.anime-group>
    @empty
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center text-base-content/60 py-8">No hay temporadas registradas.</div>
        </div>
    @endforelse

    <div class="mt-4">{{ $groups->links() }}</div>
</x-layouts.admin>
