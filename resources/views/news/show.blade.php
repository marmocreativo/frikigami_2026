<x-layouts.app :title="$news->seo_title">
    <x-slot:head>
        <meta name="description" content="{{ $news->seo_description }}">
        <link rel="canonical" href="{{ route('news.show', $news) }}">

        <meta property="og:type" content="article">
        <meta property="og:site_name" content="Frikigami">
        <meta property="og:locale" content="es_MX">
        <meta property="og:title" content="{{ $news->seo_title }}">
        <meta property="og:description" content="{{ $news->seo_description }}">
        <meta property="og:url" content="{{ route('news.show', $news) }}">
        @if ($news->cover_image)
            <meta property="og:image" content="{{ url(Storage::url($news->cover_image)) }}">
        @endif
        @if ($news->published_at)
            <meta property="article:published_time" content="{{ $news->published_at->toIso8601String() }}">
        @endif

        <meta name="twitter:card" content="{{ $news->cover_image ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $news->seo_title }}">
        <meta name="twitter:description" content="{{ $news->seo_description }}">

        <script type="application/ld+json">
            {!! json_encode(array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => \Illuminate\Support\Str::limit($news->seo_title, 110, ''),
                'description' => $news->seo_description,
                'datePublished' => $news->published_at?->toIso8601String(),
                'dateModified' => $news->updated_at->toIso8601String(),
                'author' => [['@type' => 'Person', 'name' => $news->user->name ?? 'Frikigami']],
                'publisher' => ['@type' => 'Organization', 'name' => 'Frikigami'],
                'mainEntityOfPage' => route('news.show', $news),
                'image' => $news->cover_image ? [url(Storage::url($news->cover_image))] : null,
            ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>
    <x-container class="pt-28">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            {{-- Contenido principal: 3/4 --}}
            <div class="lg:col-span-3">
                @if ($news->cover_image)
                    <img src="{{ Storage::url($news->cover_image) }}" alt="{{ $news->title }}" class="w-full h-64 object-cover rounded-box shadow mb-6">
                @endif

                <h1 class="text-3xl font-bold mb-2">{{ $news->title }}</h1>

                <div class="flex items-center gap-3 text-sm text-base-content/60 mb-4">
                    <span>Por {{ $news->user->name ?? 'Desconocido' }}</span>
                    @if ($news->published_at)
                        <span>·</span>
                        <span>{{ $news->published_at->format('d \d\e F \d\e Y') }}</span>
                    @endif
                </div>

                @if ($news->animes->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach ($news->animes as $anime)
                            <a href="{{ route('animes.show', $anime) }}" class="badge badge-outline hover:badge-primary transition-colors">
                                {{ $anime->title }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="divider"></div>

                <div class="news-body max-w-none mb-8">
                    {!! $news->body_html !!}
                </div>

                <div class="divider"></div>

                <div>
                    <h2 class="text-xl font-bold mb-4">Comentarios ({{ $news->comments->count() }})</h2>

                    @if ($news->comments->isEmpty())
                        <p class="text-base-content/60 mb-6">Sé el primero en comentar.</p>
                    @else
                        <div class="space-y-4 mb-6">
                            @foreach ($news->comments as $comment)
                                <div class="flex gap-3">
                                    <div class="w-9 h-9 rounded-full bg-neutral text-neutral-content flex items-center justify-center text-sm shrink-0">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-base-200 rounded-box p-3">
                                            <p class="text-sm font-medium">{{ $comment->user->name }}</p>
                                            <p class="text-sm mt-1">{{ $comment->body }}</p>
                                        </div>
                                        <p class="text-xs text-base-content/60 mt-1 ml-1">{{ $comment->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('comments.store') }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="commentable_type" value="App\Models\News">
                            <input type="hidden" name="commentable_id" value="{{ $news->id }}">
                            <div class="form-control">
                                <textarea name="body" rows="3" placeholder="Escribe un comentario..."
                                          class="textarea textarea-bordered w-full @error('body') textarea-error @enderror">{{ old('body') }}</textarea>
                                @error('body')
                                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Comentar</button>
                        </form>
                    @else
                        <p class="text-sm text-base-content/60">
                            <a href="{{ route('login') }}" class="link link-primary">Inicia sesión</a> para comentar.
                        </p>
                    @endauth
                </div>
            </div>

            {{-- Sidebar: 1/4 --}}
            <aside class="lg:col-span-1 space-y-8 bg-base-100 rounded-box p-6 h-fit">

                @if ($relatedNews->isNotEmpty())
                    <div>
                        <h3 class="font-bold text-sm uppercase tracking-wide text-base-content/60 mb-3">
                            Noticias relacionadas
                        </h3>
                        <ul class="space-y-3">
                            @foreach ($relatedNews as $related)
                                <li>
                                    <a href="{{ route('news.show', $related) }}" class="flex items-center gap-3 group">
                                        <div class="w-14 h-14 rounded-field overflow-hidden shrink-0">
                                            @if ($related->cover_image)
                                                <img src="{{ Storage::url($related->cover_image) }}" alt="{{ $related->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-base-300 flex items-center justify-center text-lg text-base-content/30">📰</div>
                                            @endif
                                        </div>
                                        <span class="text-sm text-base-content/80 group-hover:text-primary transition-colors leading-tight">
                                            {{ $related->title }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($latestNews->isNotEmpty())
                    <div>
                        <h3 class="font-bold text-sm uppercase tracking-wide text-base-content/60 mb-3">
                            Últimas noticias
                        </h3>
                        <ul class="space-y-3">
                            @foreach ($latestNews as $latest)
                                <li>
                                    <a href="{{ route('news.show', $latest) }}" class="flex items-center gap-3 group">
                                        <div class="w-14 h-14 rounded-field overflow-hidden shrink-0">
                                            @if ($latest->cover_image)
                                                <img src="{{ Storage::url($latest->cover_image) }}" alt="{{ $latest->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-base-300 flex items-center justify-center text-lg text-base-content/30">📰</div>
                                            @endif
                                        </div>
                                        <span class="text-sm text-base-content/80 group-hover:text-primary transition-colors leading-tight">
                                            {{ $latest->title }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </aside>

        </div>
    </x-container>
</x-layouts.app>