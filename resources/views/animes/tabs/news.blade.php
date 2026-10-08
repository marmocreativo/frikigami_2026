@if ($news->isEmpty())
    <p class="text-base-content/60">No hay noticias relacionadas con este anime.</p>
@else
    <div class="space-y-4">
        @foreach ($news as $item)
            <a href="{{ route('news.show', $item) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow block">
                <div class="card-body p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-bold">{{ $item->title }}</h3>
                            <p class="text-sm text-base-content/60 mt-1">
                                Por {{ $item->user->name ?? 'Desconocido' }}
                                · {{ $item->published_at->format('d/m/Y') }}
                            </p>
                        </div>
                        @if ($item->cover_image)
                            <img src="{{ Storage::url($item->cover_image) }}" alt="{{ $item->title }}" class="w-20 h-16 object-cover rounded shrink-0">
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $news->links() }}
    </div>
@endif