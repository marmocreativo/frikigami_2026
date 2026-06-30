<x-layouts.admin title="Temporadas">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Temporadas del año</h1>
        <a href="{{ route('admin.seasons.create') }}" class="btn btn-primary btn-sm">+ Nueva Temporada</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Temporada</th>
                        <th>Año</th>
                        <th>Animes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($seasons as $season)
                        <tr>
                            <td class="font-medium">{{ $season->name }}</td>
                            <td>{{ $season->year }}</td>
                            <td>{{ $season->animes_count }}</td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('animes.index', ['season' => $season->id]) }}"
                                       class="btn btn-ghost btn-xs" target="_blank">Ver animes</a>
                                    <a href="{{ route('admin.seasons.edit', $season) }}"
                                       class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.seasons.destroy', $season) }}"
                                          onsubmit="return confirm('¿Eliminar esta temporada? Los animes asociados quedarán sin temporada.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-base-content/60 py-8">No hay temporadas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $seasons->links() }}</div>
</x-layouts.admin>