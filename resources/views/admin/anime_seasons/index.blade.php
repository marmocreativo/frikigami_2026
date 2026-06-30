<x-layouts.admin title="Temporadas de serie">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Temporadas de serie</h1>
        <a href="{{ route('admin.anime-seasons.create') }}" class="btn btn-primary btn-sm">+ Nueva Temporada</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Anime</th>
                        <th>#</th>
                        <th>Título</th>
                        <th>Año</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($animeSeasons as $animeSeason)
                        <tr>
                            <td class="font-medium">{{ $animeSeason->anime->title }}</td>
                            <td>{{ $animeSeason->number }}</td>
                            <td>{{ $animeSeason->title ?: '—' }}</td>
                            <td>{{ $animeSeason->year ?? '—' }}</td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('admin.anime-seasons.edit', $animeSeason) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.anime-seasons.destroy', $animeSeason) }}" onsubmit="return confirm('¿Eliminar esta temporada? Se eliminarán también sus episodios.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60 py-8">No hay temporadas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $animeSeasons->links() }}</div>
</x-layouts.admin>