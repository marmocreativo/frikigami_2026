<x-layouts.admin title="Episodios">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            @if ($currentAnime)
                <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Animes</a>
            @endif
            <h1 class="text-2xl font-bold">
                {{ $currentAnime ? 'Episodios de ' . $currentAnime->title : 'Episodios' }}
            </h1>
        </div>
        <a href="{{ route('admin.episodios.create', array_filter(['anime' => $currentAnime?->slug, 'anime_season' => $currentSeason?->id])) }}"
           class="btn btn-primary btn-sm">+ Nuevo Episodio</a>
    </div>

    @include('admin.partials.alert')

    <form method="GET" action="{{ route('admin.episodios.index') }}" class="card bg-base-100 shadow-sm mb-4">
        <div class="card-body p-4 flex-row flex-wrap items-center gap-3">
            <x-admin.anime-filter :animes="$animes" :current="$currentAnime?->slug" />

            @if ($currentAnime)
                <select name="anime_season" class="select select-sm w-full sm:w-52" onchange="this.form.submit()">
                    <option value="">Todas las temporadas</option>
                    @foreach ($animeSeasons as $animeSeason)
                        <option value="{{ $animeSeason->id }}" @selected($currentSeason?->id === $animeSeason->id)>
                            {{ $animeSeason->label }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('admin.episodios.index') }}" class="btn btn-ghost btn-sm">Limpiar</a>
            @endif
        </div>
    </form>

    @forelse ($groups as $anime)
        <x-admin.anime-group :anime="$anime">
            <div class="space-y-4">
                @foreach ($anime->animeSeasons as $animeSeason)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="font-semibold">
                                {{ $animeSeason->label }}
                                <span class="text-sm font-normal text-base-content/60">· {{ $animeSeason->episodes_count }} episodios</span>
                            </h2>
                            <div class="flex gap-2">
                                @if ($animeSeason->anilist_id || $animeSeason->mal_id)
                                    {{-- Importa o completa título, resumen, fecha e imagen previa (no pisa lo que ya tienes) --}}
                                    <form method="POST" action="{{ route('admin.animes.anilist.episodios', [$anime, $animeSeason]) }}"
                                          onsubmit="return confirm('Se importarán o completarán los episodios de esta temporada. Puede tardar un poco. ¿Continuar?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-xs">Importar episodios</button>
                                    </form>
                                @endif
                                @unless ($currentAnime)
                                    <a href="{{ route('admin.episodios.index', ['anime' => $anime->slug, 'anime_season' => $animeSeason->id]) }}"
                                       class="btn btn-ghost btn-xs">Ver episodios</a>
                                @endunless
                            </div>
                        </div>

                        @if ($currentAnime)
                            @if ($animeSeason->episodes->isEmpty())
                                <p class="text-sm text-base-content/60">Sin episodios.</p>
                            @else
                                <div class="overflow-x-auto max-h-[32rem] overflow-y-auto">
                                    <table class="table table-zebra table-sm table-pin-rows">
                                        <thead>
                                            <tr>
                                                <th class="w-24"></th>
                                                <th>#</th>
                                                <th>Título</th>
                                                <th>Fecha emisión</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($animeSeason->episodes as $episode)
                                                <tr>
                                                    <td>
                                                        @if ($episode->thumbnail_url)
                                                            <img src="{{ $episode->thumbnail_url }}" alt="" loading="lazy" class="w-20 h-12 object-cover rounded">
                                                        @else
                                                            <div class="w-20 h-12 rounded bg-base-300"></div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $episode->number }}</td>
                                                    <td>{{ $episode->title ?: '—' }}</td>
                                                    <td>{{ $episode->air_date?->format('d/m/Y') ?? '—' }}</td>
                                                    <td>
                                                        <div class="flex gap-2 justify-end">
                                                            <a href="{{ route('admin.episodios.edit', $episode) }}" class="btn btn-outline btn-xs">Editar</a>
                                                            <form method="POST" action="{{ route('admin.episodios.destroy', $episode) }}" onsubmit="return confirm('¿Eliminar este episodio?');">
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
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </x-admin.anime-group>
    @empty
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center text-base-content/60 py-8">No hay episodios registrados.</div>
        </div>
    @endforelse

    <div class="mt-4">{{ $groups->links() }}</div>
</x-layouts.admin>
