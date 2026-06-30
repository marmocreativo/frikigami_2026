<x-layouts.admin title="Animes">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Animes</h1>
        <a href="{{ route('admin.animes.create') }}" class="btn btn-primary btn-sm">+ Nuevo Anime</a>
    </div>

    @include('admin.partials.alert')

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
                    @forelse ($animes as $anime)
                        <tr>
                            <td class="font-medium">{{ $anime->title }}</td>
                            <td>{{ $anime->season?->label ?? '—' }}</td>
                            <td>
                                @php $statusMap = ['upcoming' => 'Próximamente', 'ongoing' => 'En emisión', 'finished' => 'Finalizado']; @endphp
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
                            <td colspan="5" class="text-center text-base-content/60 py-8">No hay animes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $animes->links() }}</div>
</x-layouts.admin>