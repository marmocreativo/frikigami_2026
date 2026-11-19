<x-layouts.admin title="Franquicia en AniList">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.anilist.buscar') }}" class="btn btn-ghost btn-sm">← Buscar</a>
            <h1 class="text-2xl font-bold">Franquicia en AniList</h1>
        </div>
        <a href="{{ route('admin.anilist.franquicia', ['id' => $rootId, 'actualizar' => 1]) }}" class="btn btn-outline btn-sm">Actualizar desde AniList</a>
    </div>

    @include('admin.partials.alert')

    @if ($errors->any())
        <div class="alert alert-error mb-4"><span>{{ $errors->first() }}</span></div>
    @endif

    @if ($error)
        <div class="alert alert-error"><span>No se pudo consultar AniList: {{ $error }}</span></div>
    @else
        @php $faltan = $entries->whereNull('local')->count(); @endphp

        <div class="alert alert-info mb-4">
            <span class="text-sm">
                Estas son todas las entradas conectadas por precuela y secuela, en orden de estreno.
                AniList no maneja sagas: cada serie, película o parte es una entrada.
            </span>
        </div>

        <form method="POST" action="{{ route('admin.anilist.importar', $rootId) }}">
            @csrf

            <div class="card bg-base-100 shadow-sm mb-4">
                <div class="card-body p-4 gap-3">
                    <div class="flex flex-wrap items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modo" value="franquicia" class="radio radio-sm" @checked(old('modo', 'franquicia') === 'franquicia')>
                            <span class="text-sm">Un solo anime, las entradas como sus temporadas</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="modo" value="separados" class="radio radio-sm" @checked(old('modo') === 'separados')>
                            <span class="text-sm">Cada entrada como anime independiente</span>
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm font-medium">Anime padre:</span>
                        <select name="parent_id" class="select select-sm w-full sm:w-72">
                            <option value="">Crear uno nuevo con la primera entrada elegida</option>
                            @foreach ($animes as $anime)
                                <option value="{{ $anime->id }}" @selected((string) old('parent_id', $parentDefault?->id) === (string) $anime->id)>
                                    {{ $anime->title }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-base-content/60">Solo aplica al modo «un solo anime».</span>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                @if ($faltan > 0)
                    <div class="flex flex-wrap items-center justify-between gap-2 p-4 pb-0">
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" class="checkbox checkbox-sm"
                                   onclick="const on = this.checked; [...document.querySelectorAll('.import-checkbox')].slice(0, 15).forEach(c => c.checked = on)">
                            Seleccionar todas las que faltan (máx. 15 por vez)
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">Importar seleccionadas</button>
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
                                <th>Año</th>
                                <th>Eps.</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entries as $item)
                                <tr @class(['bg-base-200/50' => $item['anilist_id'] === $rootId])>
                                    <td>
                                        @unless ($item['local'])
                                            <input type="checkbox" name="ids[]" value="{{ $item['anilist_id'] }}"
                                                   class="checkbox checkbox-sm import-checkbox"
                                                   @checked(in_array($item['anilist_id'], array_map('intval', (array) old('ids', []))))>
                                        @endunless
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
                                    </td>
                                    <td><span class="badge badge-ghost badge-sm">{{ $formatLabels[$item['format']] ?? '—' }}</span></td>
                                    <td>{{ $item['year'] ?? '—' }}</td>
                                    <td>{{ $item['episodes'] ?? '—' }}</td>
                                    <td>
                                        @if ($item['local'])
                                            <span class="badge badge-success badge-sm">En tu base</span>
                                            <div class="text-xs text-base-content/60 mt-1">
                                                {{ $item['local']['anime']->title }}@if ($item['local']['temporada']) · Temporada {{ $item['local']['temporada']->number }}@endif
                                            </div>
                                        @else
                                            <span class="badge badge-error badge-sm">Falta</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    @endif
</x-layouts.admin>
