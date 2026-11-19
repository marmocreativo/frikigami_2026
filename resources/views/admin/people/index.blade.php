<x-layouts.admin title="Personas">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Animes</a>
            @endif
            <h1 class="text-2xl font-bold">
                {{ $currentAnime ? 'Reparto de ' . $currentAnime->title : 'Personas' }}
            </h1>
        </div>

        <div class="flex gap-2">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.anilist.reparto', $currentAnime) }}" class="btn btn-outline btn-sm">Buscar en AniList</a>
                <a href="{{ route('admin.animes.show', ['anime' => $currentAnime->slug, 'tab' => 'cast']) }}"
                   class="btn btn-outline btn-sm">Asignar staff</a>
            @endif
            <a href="{{ route('admin.personas.create') }}" class="btn btn-primary btn-sm">+ Nueva Persona</a>
        </div>
    </div>

    @include('admin.partials.alert')

    <form method="GET" action="{{ route('admin.personas.index') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row flex-wrap items-center gap-3">
            <label class="input input-sm w-full sm:w-64">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre..." />
            </label>

            <x-admin.anime-filter :animes="$animes" :current="$currentAnime?->slug" />

            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>

            @if (request()->anyFilled(['q', 'anime']))
                <a href="{{ route('admin.personas.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            @endif
        </div>
    </form>

    @forelse ($groups as $anime)
        @php $list = $entries->get($anime->id, collect()); @endphp

        <x-admin.anime-group :anime="$anime">
            <h2 class="font-semibold mb-2">{{ $list->count() }} {{ $list->count() === 1 ? 'persona' : 'personas' }}</h2>

            <div class="overflow-x-auto max-h-[32rem] overflow-y-auto">
                <table class="table table-zebra table-sm table-pin-rows">
                    <thead>
                        <tr>
                            <th class="w-12"></th>
                            <th>Nombre</th>
                            <th>En este anime</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list as $entry)
                            @php $person = $entry['person']; @endphp
                            <tr>
                                <td>
                                    @if ($person->photo)
                                        <img src="{{ Storage::url($person->photo) }}" alt="" loading="lazy" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-base-300"></div>
                                    @endif
                                </td>
                                <td class="font-medium">{{ $person->name }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (array_unique($entry['roles'] ?? []) as $role)
                                            <span class="badge badge-xs badge-secondary">{{ $role }}</span>
                                        @endforeach
                                        @foreach (array_unique($entry['characters'] ?? []) as $character)
                                            <span class="badge badge-xs badge-primary">Voz de {{ $character }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('personas.show', $person) }}" class="btn btn-ghost btn-xs" target="_blank">Ver</a>
                                        <a href="{{ route('admin.personas.edit', $person) }}" class="btn btn-outline btn-xs">Editar</a>
                                        <form method="POST" action="{{ route('admin.personas.destroy', $person) }}" onsubmit="return confirm('¿Eliminar esta persona?');">
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
        @if ($orphans->isEmpty())
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center text-base-content/60 py-8">No se encontraron personas.</div>
            </div>
        @endif
    @endforelse

    @if ($orphans->isNotEmpty())
        <x-admin.anime-group title="Sin anime asignado">
            <h2 class="font-semibold mb-2">{{ $orphans->count() }} {{ $orphans->count() === 1 ? 'persona' : 'personas' }}</h2>
            <div class="overflow-x-auto max-h-[32rem] overflow-y-auto">
                <table class="table table-zebra table-sm table-pin-rows">
                    <tbody>
                        @foreach ($orphans as $person)
                            <tr>
                                <td class="font-medium">{{ $person->name }}</td>
                                <td>
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('personas.show', $person) }}" class="btn btn-ghost btn-xs" target="_blank">Ver</a>
                                        <a href="{{ route('admin.personas.edit', $person) }}" class="btn btn-outline btn-xs">Editar</a>
                                        <form method="POST" action="{{ route('admin.personas.destroy', $person) }}" onsubmit="return confirm('¿Eliminar esta persona?');">
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
    @endif

    <div class="mt-4">{{ $groups->links() }}</div>
</x-layouts.admin>
