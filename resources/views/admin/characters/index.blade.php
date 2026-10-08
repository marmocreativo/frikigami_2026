<x-layouts.admin title="Personajes">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Animes</a>
            @endif
            <h1 class="text-2xl font-bold">
                {{ $currentAnime ? 'Personajes de ' . $currentAnime->title : 'Personajes' }}
            </h1>
        </div>

        <div class="flex gap-2">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.show', ['anime' => $currentAnime->slug, 'tab' => 'characters']) }}"
                   class="btn btn-outline btn-sm">Asignar a este anime</a>
            @endif
            <a href="{{ route('admin.characters.create') }}" class="btn btn-primary btn-sm">+ Nuevo Personaje</a>
        </div>
    </div>

    @include('admin.partials.alert')

    <form method="GET" action="{{ route('admin.characters.index') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row flex-wrap items-center gap-3">
            <label class="input input-sm w-full sm:w-64">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre..." />
            </label>

            <x-admin.anime-filter :animes="$animes" :current="$currentAnime?->slug" />

            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>

            @if (request()->anyFilled(['q', 'anime']))
                <a href="{{ route('admin.characters.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            @endif
        </div>
    </form>

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