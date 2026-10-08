<x-layouts.admin title="Personas">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Animes</a>
            @endif
            <h1 class="text-2xl font-bold">
                {{ $currentAnime ? 'Cast de ' . $currentAnime->title : 'Personas' }}
            </h1>
        </div>

        <div class="flex gap-2">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.show', ['anime' => $currentAnime->slug, 'tab' => 'cast']) }}"
                   class="btn btn-outline btn-sm">Asignar staff</a>
            @endif
            <a href="{{ route('admin.people.create') }}" class="btn btn-primary btn-sm">+ Nueva Persona</a>
        </div>
    </div>

    @include('admin.partials.alert')

    <form method="GET" action="{{ route('admin.people.index') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row flex-wrap items-center gap-3">
            <label class="input input-sm w-full sm:w-64">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre..." />
            </label>

            <x-admin.anime-filter :animes="$animes" :current="$currentAnime?->slug" />

            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>

            @if (request()->anyFilled(['q', 'anime']))
                <a href="{{ route('admin.people.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Seiyuu</th>
                        <th>Staff</th>
                        @if ($currentAnime)
                            <th>En este anime</th>
                        @endif
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($people as $person)
                        <tr>
                            <td class="font-medium">{{ $person->name }}</td>
                            <td>{{ $person->is_voice_actor ? '✓' : '—' }}</td>
                            <td>{{ $person->is_staff ? '✓' : '—' }}</td>
                            @if ($currentAnime)
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($person->animes as $anime)
                                            <span class="badge badge-xs badge-secondary">{{ $anime->pivot->role ?: 'Staff' }}</span>
                                        @endforeach
                                        @foreach ($person->characters as $character)
                                            <span class="badge badge-xs badge-primary">Voz de {{ $character->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            @endif
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
                            <td colspan="{{ $currentAnime ? 5 : 4 }}" class="text-center text-base-content/60 py-8">No se encontraron personas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $people->links() }}</div>
</x-layouts.admin>