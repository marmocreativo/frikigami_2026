@if ($anime->animeSeasons->isEmpty())
    <p class="text-base-content/60">No hay episodios registrados para este anime.</p>
@else
    <div class="space-y-6">
        @foreach ($anime->animeSeasons as $animeSeason)
            <div>
                <h3 class="text-lg font-bold mb-3">{{ $animeSeason->label }}</h3>

                @if ($animeSeason->episodes->isEmpty())
                    <p class="text-base-content/60 text-sm">Sin episodios registrados.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-zebra table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Título</th>
                                    <th>Fecha de emisión</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($animeSeason->episodes->sortBy('number') as $episode)
                                    <tr>
                                        <td class="w-12 text-base-content/60">{{ $episode->number }}</td>
                                        <td>{{ $episode->title ?: 'Sin título' }}</td>
                                        <td class="text-base-content/60">{{ $episode->air_date?->format('d/m/Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if (!$loop->last)
                <div class="divider"></div>
            @endif
        @endforeach
    </div>
@endif