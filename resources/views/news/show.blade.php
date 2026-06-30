<x-layouts.app :title="$news->title">
    <div class="max-w-3xl mx-auto">
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

        <div class="prose max-w-none mb-8">
            {!! nl2br(e($news->body)) !!}
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
</x-layouts.app>