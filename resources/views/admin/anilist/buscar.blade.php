<x-layouts.admin title="Buscar en AniList">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Animes</a>
        <h1 class="text-2xl font-bold">Buscar en AniList</h1>
    </div>

    @include('admin.partials.alert')

    <form method="GET" action="{{ route('admin.anilist.buscar') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row items-center gap-3">
            <label class="input w-full">
                <input type="search" name="q" value="{{ $q }}" placeholder="Nombre del anime (ej. Dragon Ball)" autofocus />
            </label>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    @if ($error)
        <div class="alert alert-error"><span>No se pudo consultar AniList: {{ $error }}</span></div>
    @elseif ($q === '')
        <p class="text-base-content/60">Escribe un nombre. Al elegir un resultado verás toda su franquicia (precuelas y secuelas) para importarla.</p>
    @elseif ($results->isEmpty())
        <p class="text-base-content/60">AniList no devolvió resultados para «{{ $q }}».</p>
    @else
        <div class="card bg-base-100 shadow-sm">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <tbody>
                        @foreach ($results as $item)
                            <tr>
                                <td class="w-14">
                                    @if ($item['thumb'])
                                        <img src="{{ $item['thumb'] }}" alt="" loading="lazy" class="w-10 h-14 object-cover rounded">
                                    @endif
                                </td>
                                <td>
                                    <div class="font-medium">{{ $item['title'] }}</div>
                                    @if ($item['romaji'] && $item['romaji'] !== $item['title'])
                                        <div class="text-xs text-base-content/60">{{ $item['romaji'] }}</div>
                                    @endif
                                </td>
                                <td><span class="badge badge-ghost badge-sm">{{ $formatLabels[$item['format']] ?? '—' }}</span></td>
                                <td class="text-sm">{{ $item['year'] ?? '—' }}</td>
                                <td class="text-sm">{{ $item['episodes'] ? $item['episodes'].' eps.' : '—' }}</td>
                                <td>
                                    @if ($item['local'])
                                        <span class="badge badge-success badge-sm">En tu base</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.anilist.franquicia', $item['anilist_id']) }}" class="btn btn-primary btn-xs">Ver franquicia</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.admin>
