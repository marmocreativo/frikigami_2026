<x-layouts.app title="Noticias">
    <x-container class="pt-28">
        <div class="flex items-center justify-between mb-6">
            <form method="GET" action="{{ route('news.index') }}" class="mb-6">
                <label class="input w-full">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar noticias..." />
                </label>
            </form>
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
                    <a href="{{ route('news.show', $item) }}" class="hover:-translate-y-0.5 transition-all">
                        <div class="relative rounded-box overflow-hidden">
                            @if ($item->cover_image)
                                <img src="{{ Storage::url($item->cover_image) }}" alt="{{ $item->title }}" class="h-48 w-full object-cover">
                            @else
                                <div class="h-48 w-full bg-base-300 flex items-center justify-center text-base-content/30 text-5xl">
                                    📰
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1 mt-3">
                            @foreach ($item->animes as $anime)
                                <span class="badge badge-sm badge-outline">{{ $anime->title }}</span>
                            @endforeach
                        </div>

                        <h2 class="font-bold text-base leading-tight text-base-content mt-2">{{ $item->title }}</h2>

                        <p class="text-sm text-base-content/60 mt-1">
                            {{ $item->user->name ?? 'Desconocido' }}
                            · {{ $item->published_at->format('d/m/Y') }}
                        </p>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $news->links() }}
            </div>
        @endif
    </x-container>
</x-layouts.app>