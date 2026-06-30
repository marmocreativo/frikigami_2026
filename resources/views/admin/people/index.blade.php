<x-layouts.admin title="Personas">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Personas</h1>
        <a href="{{ route('admin.people.create') }}" class="btn btn-primary btn-sm">+ Nueva Persona</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Seiyuu</th>
                        <th>Staff</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($people as $person)
                        <tr>
                            <td class="font-medium">{{ $person->name }}</td>
                            <td>{{ $person->is_voice_actor ? '✓' : '—' }}</td>
                            <td>{{ $person->is_staff ? '✓' : '—' }}</td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('people.show', $person) }}" class="btn btn-ghost btn-xs" target="_blank">Ver</a>
                                    <a href="{{ route('admin.people.edit', $person) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.people.destroy', $person) }}" onsubmit="return confirm('¿Eliminar esta persona?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-base-content/60 py-8">No hay personas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $people->links() }}</div>
</x-layouts.admin>