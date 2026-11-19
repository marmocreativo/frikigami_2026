@props(['anime' => null, 'title' => null])

{{-- Grupo por anime: portada a la izquierda, contenido (slot) a la derecha --}}
<div class="card bg-base-100 shadow-sm mb-4">
    <div class="card-body p-4 flex-col md:flex-row gap-4">
        <div class="shrink-0 md:w-40">
            @if ($anime)
                <a href="{{ route('admin.animes.edit', $anime) }}" class="block">
                    @if ($anime->cover_image)
                        <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" loading="lazy"
                             class="w-full max-w-40 aspect-[3/4] object-cover rounded-box shadow">
                    @else
                        <div class="w-full max-w-40 aspect-[3/4] rounded-box bg-base-300 flex items-center justify-center text-3xl text-base-content/30">?</div>
                    @endif
                </a>
                <a href="{{ route('admin.animes.edit', $anime) }}" class="font-bold mt-2 leading-tight block hover:text-primary">{{ $anime->title }}</a>
                @if ($anime->season)
                    <p class="text-xs text-base-content/60">{{ $anime->season->label }}</p>
                @endif
            @else
                <div class="w-full max-w-40 aspect-[3/4] rounded-box bg-base-200 flex items-center justify-center text-3xl text-base-content/30">∅</div>
                <p class="font-bold mt-2 leading-tight">{{ $title }}</p>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            {{ $slot }}
        </div>
    </div>
</div>
