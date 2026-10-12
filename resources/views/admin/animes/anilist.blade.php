<x-layouts.admin :title="'AniList · ' . $anime->title">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.anime-seasons.index', ['anime' => $anime->slug]) }}" class="btn btn-ghost btn-sm">← Temporadas</a>
            <h1 class="text-2xl font-bold">{{ $anime->title }} · AniList</h1>
        </div>

        @if ($anime->anilist_id)
            <div class="flex gap-2">
                <a href="{{ route('admin.animes.anilist.cast', $anime) }}" class="btn btn-outline btn-sm">Personajes y cast</a>
                <form method="POST" action="{{ route('admin.animes.anilist.refresh', $anime) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Actualizar desde AniList</button>
                </form>
            </div>
        @endif
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

    @elseif (! $anime->anilist_id)
        {{-- Paso previo: vincular el anime con su entrada de AniList --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-4">
                <h2 class="font-semibold">Este anime aún no está vinculado a AniList</h2>
                <p class="text-sm text-base-content/60">
                    Resultados para «{{ $anime->title }}». Elige la entrada que corresponde a la
                    <strong>primera temporada</strong> (o cualquiera de la serie: se recorren precuelas y secuelas).
                </p>

                <div class="space-y-2 mt-2">
                    @forelse ($candidates as $candidate)
                        <div class="flex items-center gap-3 border border-base-300 rounded-box p-2">
                            @if ($candidate['thumb'])
                                <img src="{{ $candidate['thumb'] }}" alt="" loading="lazy" class="w-10 h-14 object-cover rounded">
                            @endif
                            <div class="flex-1">
                                <div class="font-medium">{{ $candidate['title'] }}</div>
                                <div class="text-xs text-base-content/60">
                                    {{ $candidate['romaji'] }} · {{ $formatLabels[$candidate['format']] ?? '—' }}
                                    · {{ $candidate['year'] ?? 's/f' }}
                                    · {{ $candidate['episodes'] ? $candidate['episodes'] . ' eps.' : 'eps. s/d' }}
                                </div>
                            </div>

                            @if (in_array($candidate['anilist_id'], $takenIds, true))
                                <span class="badge badge-warning badge-sm">Ya vinculado a otro anime</span>
                            @else
                                <form method="POST" action="{{ route('admin.animes.anilist.link', $anime) }}">
                                    @csrf
                                    <input type="hidden" name="anilist_id" value="{{ $candidate['anilist_id'] }}">
                                    <button type="submit" class="btn btn-primary btn-xs">Vincular</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-base-content/60">AniList no devolvió resultados para ese título.</p>
                    @endforelse
                </div>
            </div>
        </div>

    @else
        {{-- Filtro de formatos --}}
        <form method="GET" action="{{ route('admin.animes.anilist', $anime) }}" class="card bg-base-100 shadow-sm mb-4">
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

        {{-- Formulario de importación (los checkboxes se asocian con form="import-form") --}}
        <form id="import-form" method="POST" action="{{ route('admin.animes.anilist.seasons', $anime) }}">
            @csrf
            @foreach ($formats as $format)
                <input type="hidden" name="formats[]" value="{{ $format }}">
            @endforeach
        </form>

        <div class="card bg-base-100 shadow-sm">
            @if ($rows->where('state', 'missing')->isNotEmpty())
                <div class="flex flex-wrap items-center justify-end gap-4 p-4 pb-0">
                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="checkbox" form="import-form" name="with_episodes" value="1" class="checkbox checkbox-sm" checked>
                        Importar también los episodios
                    </label>
                    <button type="submit" form="import-form" class="btn btn-primary btn-sm">Importar seleccionadas</button>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-14"></th>
                            <th>#</th>
                            <th>Temporada</th>
                            <th>Formato</th>
                            <th>Año</th>
                            <th>Eps. AniList</th>
                            <th>Eps. en tu base</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $item = $row['item'];
                                $local = $row['local'];
                            @endphp
                            <tr>
                                <td>
                                    @if ($row['state'] === 'missing')
                                        <input type="checkbox" form="import-form" name="ids[]" value="{{ $item['anilist_id'] }}"
                                               class="checkbox checkbox-sm" checked>
                                    @endif
                                </td>
                                <td>
                                    @if ($item['thumb'])
                                        <img src="{{ $item['thumb'] }}" alt="" loading="lazy" class="w-10 h-14 object-cover rounded">
                                    @endif
                                </td>
                                <td class="font-medium">{{ $local?->number ?? $row['number'] }}</td>
                                <td class="max-w-xs">
                                    <div class="font-medium">{{ $local?->label ?? ($row['title'] ?: 'Temporada ' . $row['number']) }}</div>
                                    <div class="text-xs text-base-content/60">{{ $item['title'] }}</div>
                                </td>
                                <td><span class="badge badge-ghost badge-sm">{{ $formatLabels[$item['format']] ?? '—' }}</span></td>
                                <td>{{ $item['year'] ?? '—' }}</td>
                                <td>{{ $item['episodes'] ?? '—' }}</td>
                                <td>{{ $local ? $local->episodes_count : '—' }}</td>
                                <td>
                                    @if ($row['state'] === 'linked')
                                        <span class="badge badge-success badge-sm">Cargada</span>
                                    @elseif ($row['state'] === 'possible')
                                        <span class="badge badge-info badge-sm">Posible</span>
                                        <div class="text-xs text-base-content/60 mt-1">Misma # en tu base</div>
                                    @else
                                        <span class="badge badge-error badge-sm">Falta</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-end">
                                        @if ($row['state'] === 'linked')
                                            <form method="POST" action="{{ route('admin.animes.anilist.episodes', [$anime, $local]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-xs">
                                                    {{ $local->episodes_count > 0 ? 'Completar episodios' : 'Importar episodios' }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($row['state'] === 'possible')
                                            <form method="POST" action="{{ route('admin.animes.anilist.seasons.link', $anime) }}">
                                                @csrf
                                                <input type="hidden" name="anime_season_id" value="{{ $local->id }}">
                                                <input type="hidden" name="anilist_id" value="{{ $item['anilist_id'] }}">
                                                <button type="submit" class="btn btn-outline btn-xs">Vincular</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-base-content/60 py-8">
                                    No hay entradas con los formatos elegidos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-base-content/60 mt-3">
            La numeración sigue el orden de estreno entre los formatos marcados. Si incluyes películas u OVAs,
            la numeración de las temporadas cambia.
        </p>
    @endif
</x-layouts.admin>