<x-layouts.admin title="Géneros">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Géneros</h1>
        <a href="{{ route('admin.genres.create') }}" class="btn btn-primary btn-sm">+ Nuevo Género</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Animes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($genres as $genre)
                        <tr>
                            <td class="font-medium">{{ $genre->name }}</td>
                            <td class="text-base-content/60">{{ $genre->slug }}</td>
                            <td>{{ $genre->animes_count }}</td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.genres.destroy', $genre) }}" onsubmit="return confirm('¿Eliminar este género?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-base-content/60 py-8">No hay géneros registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $genres->links() }}</div>
</x-layouts.admin>