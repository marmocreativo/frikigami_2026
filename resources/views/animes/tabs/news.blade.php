@if ($anime->news->isEmpty())
    <p class="text-base-content/60">No hay noticias relacionadas con este anime.</p>
@else
    <div class="space-y-4">
        @foreach ($anime->news->sortByDesc('published_at') as $news)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-bold">{{ $news->title }}</h3>
                            <p class="text-sm text-base-content/60 mt-1">
                                Por {{ $news->user->name ?? 'Desconocido' }}
                                @if ($news->published_at)
                                    · {{ $news->published_at->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        @if ($news->cover_image)
                            <img src="{{ Storage::url($news->cover_image) }}" alt="{{ $news->title }}" class="w-20 h-16 object-cover rounded shrink-0">
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif