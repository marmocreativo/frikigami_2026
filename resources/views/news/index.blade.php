<x-layouts.app title="Noticias">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Noticias</h1>
        @auth
            <a href="#" class="btn btn-primary btn-sm">+ Nueva Noticia</a>
        @endauth
    </div>

    @if ($news->isEmpty())
        <div class="text-center py-16">
            <p class="text-base-content/60 text-lg">No hay noticias publicadas aún.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($news as $item)
                <a href="{{ route('news.show', $item) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                    @if ($item->cover_image)
                        <figure>
                            <img src="{{ Storage::url($item->cover_image) }}" alt="{{ $item->title }}" class="h-48 w-full object-cover">
                        </figure>
                    @else
                        <div class="h-48 bg-base-300 flex items-center justify-center text-base-content/20 text-5xl">
                            📰
                        </div>
                    @endif
                    <div class="card-body p-4">
                        <h2 class="card-title text-base leading-tight">{{ $item->title }}</h2>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach ($item->animes as $anime)
                                <span class="badge badge-sm badge-outline">{{ $anime->title }}</span>
                            @endforeach
                        </div>
                        <p class="text-xs text-base-content/60 mt-2">
                            {{ $item->user->name ?? 'Desconocido' }}
                            · {{ $item->published_at->format('d/m/Y') }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $news->links() }}
        </div>
    @endif
</x-layouts.app>