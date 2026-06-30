<x-layouts.admin title="Personajes">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Personajes</h1>
        <a href="{{ route('admin.characters.create') }}" class="btn btn-primary btn-sm">+ Nuevo Personaje</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Animes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($characters as $character)
                        <tr>
                            <td class="font-medium">{{ $character->name }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($character->animes as $anime)
                                        <span class="badge badge-xs badge-outline">{{ $anime->title }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('characters.show', $character) }}" class="btn btn-ghost btn-xs" target="_blank">Ver</a>
                                    <a href="{{ route('admin.characters.edit', $character) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.characters.destroy', $character) }}" onsubmit="return confirm('¿Eliminar este personaje?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-base-content/60 py-8">No hay personajes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $characters->links() }}</div>
</x-layouts.admin>