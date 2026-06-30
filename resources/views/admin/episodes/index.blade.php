<x-layouts.admin title="Episodios">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Episodios</h1>
        <a href="{{ route('admin.episodes.create') }}" class="btn btn-primary btn-sm">+ Nuevo Episodio</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Anime</th>
                        <th>Temporada</th>
                        <th>#</th>
                        <th>Título</th>
                        <th>Fecha emisión</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($episodes as $episode)
                        <tr>
                            <td>{{ $episode->animeSeason->anime->title }}</td>
                            <td>{{ $episode->animeSeason->label }}</td>
                            <td>{{ $episode->number }}</td>
                            <td>{{ $episode->title ?: '—' }}</td>
                            <td>{{ $episode->air_date?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('admin.episodes.edit', $episode) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.episodes.destroy', $episode) }}" onsubmit="return confirm('¿Eliminar este episodio?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-base-content/60 py-8">No hay episodios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $episodes->links() }}</div>
</x-layouts.admin>