<x-layouts.admin :title="'Personajes y cast · ' . $anime->title">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.animes.anilist', $anime) }}" class="btn btn-ghost btn-sm">← AniList</a>
            <h1 class="text-2xl font-bold">{{ $anime->title }} · Personajes y cast</h1>
        </div>

        <form method="POST" action="{{ route('admin.animes.anilist.cast.refresh', $anime) }}">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">Actualizar desde AniList</button>
        </form>
    </div>

    @include('admin.partials.alert')

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @if ($error)
        <div class="alert alert-error">
            <span>No se pudo consultar AniList: {{ $error }}</span>
        </div>
    @else
        <form id="characters-form" method="POST" action="{{ route('admin.animes.anilist.cast.characters', $anime) }}">@csrf</form>
        <form id="staff-form" method="POST" action="{{ route('admin.animes.anilist.cast.staff', $anime) }}">@csrf</form>

        {{-- PERSONAJES Y ACTORES DE VOZ --}}
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body p-4 pb-0">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="font-semibold">Personajes y actores de voz</h2>
                        <p class="text-xs text-base-content/60">
                            {{ $characters->where('done', true)->count() }} de {{ $characters->count() }} ya están completos en este anime.
                            Los «posibles duplicados» no vienen marcados: si los marcas, se reutiliza el personaje existente.
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-sm"
                                   onclick="const on = this.checked; document.querySelectorAll('.character-checkbox:not(:disabled)').forEach(c => c.checked = on)">
                            Todos
                        </label>
                        <button type="submit" form="characters-form" class="btn btn-primary btn-sm">Agregar seleccionados</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-14"></th>
                            <th>Personaje</th>
                            <th>Rol</th>
                            <th>Voz (japonés)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($characters as $row)
                            @php $item = $row['item']; @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" form="characters-form" name="ids[]" value="{{ $item['anilist_id'] }}"
                                           class="checkbox checkbox-sm character-checkbox"
                                           @disabled($row['done'])
                                           @checked(! $row['done'] && $row['state'] !== 'possible')>
                                </td>
                                <td>
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="" loading="lazy" class="w-10 h-14 object-cover rounded">
                                    @endif
                                </td>
                                <td class="font-medium">{{ $item['name'] }}</td>
                                <td>
                                    <span class="badge badge-ghost badge-sm">{{ $item['role'] === 'MAIN' ? 'Principal' : 'Secundario' }}</span>
                                </td>
                                <td class="text-sm">
                                    {{ collect($item['voice_actors'])->pluck('name')->implode(', ') ?: '—' }}
                                </td>
                                <td>
                                    @if ($row['done'])
                                        <span class="badge badge-success badge-sm">En el anime</span>
                                    @elseif ($row['state'] === 'linked')
                                        <span class="badge badge-info badge-sm">Ya existe</span>
                                        <div class="text-xs text-base-content/60 mt-1">Falta agregarlo o su voz</div>
                                    @elseif ($row['state'] === 'possible')
                                        <span class="badge badge-warning badge-sm">Posible duplicado</span>
                                        <div class="text-xs text-base-content/60 mt-1">Existe: {{ $row['local']->name }}</div>
                                    @else
                                        <span class="badge badge-error badge-sm">Nuevo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/60 py-8">AniList no devolvió personajes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- STAFF --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4 pb-0">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="font-semibold">Staff de producción</h2>
                        <p class="text-xs text-base-content/60">
                            {{ $staff->where('done', true)->count() }} de {{ $staff->count() }} ya están en este anime.
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-sm"
                                   onclick="const on = this.checked; document.querySelectorAll('.staff-checkbox:not(:disabled)').forEach(c => c.checked = on)">
                            Todos
                        </label>
                        <button type="submit" form="staff-form" class="btn btn-primary btn-sm">Agregar seleccionados</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-14"></th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staff as $row)
                            @php $item = $row['item']; @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" form="staff-form" name="ids[]" value="{{ $item['anilist_id'] }}"
                                           class="checkbox checkbox-sm staff-checkbox"
                                           @disabled($row['done'])
                                           @checked(! $row['done'] && $row['state'] !== 'possible')>
                                </td>
                                <td>
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="" loading="lazy" class="w-10 h-14 object-cover rounded">
                                    @endif
                                </td>
                                <td class="font-medium">{{ $item['name'] }}</td>
                                <td class="text-sm">{{ $row['role'] }}</td>
                                <td>
                                    @if ($row['done'])
                                        <span class="badge badge-success badge-sm">En el anime</span>
                                    @elseif ($row['state'] === 'linked')
                                        <span class="badge badge-info badge-sm">Ya existe</span>
                                        <div class="text-xs text-base-content/60 mt-1">Falta agregarlo al anime</div>
                                    @elseif ($row['state'] === 'possible')
                                        <span class="badge badge-warning badge-sm">Posible duplicado</span>
                                        <div class="text-xs text-base-content/60 mt-1">Existe: {{ $row['local']->name }}</div>
                                    @else
                                        <span class="badge badge-error badge-sm">Nuevo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-base-content/60 py-8">AniList no devolvió staff.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-base-content/60 mt-3">
            Se muestran hasta 50 personajes principales y secundarios (sin personajes de fondo) y hasta 50 integrantes del staff.
            Los nombres, descripciones y roles quedan como los entrega AniList; los roles más comunes se traducen al español.
        </p>
    @endif
</x-layouts.admin>