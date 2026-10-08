<div class="space-y-4">
    <div class="prose max-w-none">
        <h3 class="text-lg font-bold">Sinopsis</h3>
        <p>{{ $anime->synopsis ?: 'Sin sinopsis disponible.' }}</p>
    </div>

    <div class="divider"></div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div>
            <p class="text-sm text-base-content/60">Estado</p>
            @php $statusMap = ['upcoming' => 'Próximamente', 'ongoing' => 'En emisión', 'finished' => 'Finalizado']; @endphp
            <p class="font-medium">{{ $statusMap[$anime->status] }}</p>
        </div>

        @if ($anime->season)
            <div>
                <p class="text-sm text-base-content/60">Temporada de estreno</p>
                <p class="font-medium">{{ $anime->season->label }}</p>
            </div>
        @endif

        <div>
            <p class="text-sm text-base-content/60">Episodios totales</p>
            <p class="font-medium">{{ $anime->episodes_count }}</p>
        </div>

        <div>
            <p class="text-sm text-base-content/60">Temporadas</p>
            <p class="font-medium">{{ $anime->anime_seasons_count }}</p>
        </div>

        @if ($anime->genres->isNotEmpty())
            <div class="col-span-2 sm:col-span-3">
                <p class="text-sm text-base-content/60 mb-1">Géneros</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($anime->genres as $genre)
                        <a href="{{ route('animes.genre', $genre->slug) }}" class="badge badge-outline hover:badge-primary transition-colors">
                            {{ $genre->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>