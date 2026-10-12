<x-layouts.admin :title="'AniList · ' . $season->label">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.animes.index', ['season' => $season->id]) }}" class="btn btn-ghost btn-sm">← Animes</a>
            <h1 class="text-2xl font-bold">{{ $season->label }} · AniList</h1>
        </div>

        <form method="POST" action="{{ route('admin.seasons.anilist.refresh', $season) }}">
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
        {{-- Filtro de formatos --}}
        <form method="GET" action="{{ route('admin.seasons.anilist', $season) }}" class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body p-4 flex-row flex-wrap items-center gap-4">
                <span class="text-sm font-medium">Formatos:</span>
                @foreach ($formatLabels as $value => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="formats[]" value="{{ $value }}" class="checkbox checkbox-sm"
                               @checked(in_array($value, $formats, true)) onchange="this.form.submit()">
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </form>

        {{-- Resumen --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body p-4 gap-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-semibold">
                        {{ $counts['linked'] + $counts['possible'] }} de {{ $counts['total'] }} ya están en tu base
                    </p>
                    <div class="flex gap-2">
                        <span class="badge badge-success badge-sm">Vinculados {{ $counts['linked'] }}</span>
                        <span class="badge badge-info badge-sm">Posibles {{ $counts['possible'] }}</span>
                        <span class="badge badge-error badge-sm">Faltan {{ $counts['missing'] }}</span>
                    </div>
                </div>
                <progress class="progress progress-primary w-full"
                          value="{{ $counts['linked'] + $counts['possible'] }}"
                          max="{{ max($counts['total'], 1) }}"></progress>
            </div>
        </div>

        {{-- Formulario de importación (los checkboxes de la tabla se asocian con form="import-form") --}}
        <form id="import-form" method="POST" action="{{ route('admin.seasons.anilist.import', $season) }}">
            @csrf
        </form>

        <div class="card bg-base-100 shadow-sm">
            @if ($counts['missing'] > 0)
                <div class="flex flex-wrap items-center justify-between gap-2 p-4 pb-0">
                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="checkbox" class="checkbox checkbox-sm"
                               onclick="const on = this.checked; [...document.querySelectorAll('.import-checkbox')].slice(0, 15).forEach(c => c.checked = on)">
                        Seleccionar faltantes (máx. 15 por vez)
                    </label>
                    <button type="submit" form="import-form" class="btn btn-primary btn-sm">Importar seleccionados</button>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-14"></th>
                            <th>Título</th>
                            <th>Formato</th>
                            <th>Eps.</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php $item = $row['item']; @endphp
                            <tr>
                                <td>
                                    @if ($row['state'] === 'missing')
                                        <input type="checkbox" form="import-form" name="ids[]" value="{{ $item['anilist_id'] }}"
                                               class="checkbox checkbox-sm import-checkbox">
                                    @endif
                                </td>
                                <td>
                                    @if ($item['thumb'])
                                        <img src="{{ $item['thumb'] }}" alt="" loading="lazy" class="w-10 h-14 object-cover rounded">
                                    @endif
                                </td>
                                <td class="max-w-md">
                                    <div class="font-medium">{{ $item['title'] }}</div>
                                    @if ($item['romaji'] && $item['romaji'] !== $item['title'])
                                        <div class="text-xs text-base-content/60">{{ $item['romaji'] }}</div>
                                    @endif
                                    <p class="text-xs text-base-content/60 line-clamp-2 mt-1">
                                        {{ $item['synopsis'] ?? 'Sin sinopsis.' }}
                                    </p>
                                </td>
                                <td>
                                    <span class="badge badge-ghost badge-sm">{{ $formatLabels[$item['format']] ?? '—' }}</span>
                                </td>
                                <td>{{ $item['episodes'] ?? '—' }}</td>
                                <td>
                                    @if ($row['state'] === 'linked')
                                        <span class="badge badge-success badge-sm">Cargado</span>
                                        @if ($row['anime']->season_id !== $season->id)
                                            <span class="badge badge-warning badge-xs">Otra temporada</span>
                                        @endif
                                    @elseif ($row['state'] === 'possible')
                                        <span class="badge badge-info badge-sm">Posible</span>
                                        <div class="text-xs text-base-content/60 mt-1">{{ $row['anime']->title }}</div>
                                    @else
                                        <span class="badge badge-error badge-sm">Falta</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-end">
                                        @if ($row['state'] === 'linked')
                                            <a href="{{ route('admin.animes.edit', $row['anime']) }}" class="btn btn-ghost btn-xs">Editar</a>
                                        @elseif ($row['state'] === 'possible')
                                            <form method="POST" action="{{ route('admin.seasons.anilist.link', $season) }}">
                                                @csrf
                                                <input type="hidden" name="anime_id" value="{{ $row['anime']->id }}">
                                                <input type="hidden" name="anilist_id" value="{{ $item['anilist_id'] }}">
                                                <button type="submit" class="btn btn-outline btn-xs">Vincular</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/60 py-8">
                                    AniList no devolvió animes para esta temporada con los formatos elegidos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($unmatchedLocal->isNotEmpty())
            <div class="card bg-base-100 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h2 class="font-semibold">En tu base, sin coincidencia en AniList</h2>
                    <p class="text-sm text-base-content/60">
                        Asignados a {{ $season->label }} pero que no aparecen en esta lista. Revisa si el título
                        difiere o si pertenecen a otra temporada.
                    </p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach ($unmatchedLocal as $anime)
                            <a href="{{ route('admin.animes.edit', $anime) }}" class="badge badge-outline">{{ $anime->title }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-layouts.admin>