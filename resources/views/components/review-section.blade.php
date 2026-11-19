{{-- Calificación y comentarios --}}
<section id="calificaciones" class="space-y-6">
    <h3 class="text-lg font-bold">Calificación y comentarios</h3>

    {{-- Promedio --}}
    <div class="flex items-center gap-4">
        <x-stars :value="$display" size="text-3xl" />
        <div>
            <p class="font-bold text-xl leading-none">{{ number_format($display, 1) }} <span class="text-sm font-normal text-base-content/60">/ 5</span></p>
            <p class="text-sm text-base-content/60 mt-1">
                @if ($count)
                    {{ $count }} {{ $count === 1 ? 'calificación' : 'calificaciones' }}
                @else
                    Aún sin calificaciones. ¡Sé el primero!
                @endif
            </p>
        </div>
    </div>

    {{-- Formulario --}}
    @auth
        <form method="POST" action="{{ route('calificaciones.store') }}" class="bg-base-100 rounded-box p-4 space-y-3">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="id" value="{{ $item->id }}">

            @php $selected = (int) old('stars', $userStars ?? $defaultStars); @endphp

            <div>
                <p class="text-sm font-medium mb-1">
                    {{ $userStars ? 'Tu calificación (puedes cambiarla)' : 'Tu calificación' }}
                </p>
                <div class="rating rating-lg" role="radiogroup" aria-label="Calificación de 1 a 5 estrellas">
                    @foreach (range(1, 5) as $n)
                        <input type="radio" name="stars" value="{{ $n }}" class="mask mask-star-2 bg-warning"
                               aria-label="{{ $n }} {{ $n === 1 ? 'estrella' : 'estrellas' }}" @checked($selected === $n)>
                    @endforeach
                </div>
                @error('stars')<span class="text-error text-sm block mt-1">{{ $message }}</span>@enderror
            </div>

            <div class="form-control">
                <textarea name="body" rows="3" placeholder="Deja un comentario (opcional)..."
                          class="textarea textarea-bordered w-full @error('body') textarea-error @enderror">{{ old('body') }}</textarea>
                @error('body')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn btn-primary btn-sm">{{ $userStars ? 'Actualizar' : 'Calificar' }}</button>
        </form>
    @else
        <p class="text-sm text-base-content/60">
            <a href="{{ route('login') }}" class="link link-primary">Inicia sesión</a> para calificar y comentar.
        </p>
    @endauth

    {{-- Comentarios --}}
    @if ($comments->isNotEmpty())
        <div class="space-y-4">
            @foreach ($comments as $comment)
                <div class="flex gap-3">
                    @if ($comment->user->avatar_url)
                        <img src="{{ $comment->user->avatar_url }}" alt="" referrerpolicy="no-referrer" loading="lazy"
                             class="w-9 h-9 rounded-full object-cover shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-full bg-neutral text-neutral-content flex items-center justify-center text-sm shrink-0">
                            {{ $comment->user->initials }}
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="bg-base-200 rounded-box p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-medium">{{ $comment->user->full_name }}</p>
                                @if (isset($starsByUser[$comment->user_id]))
                                    <x-stars :value="$starsByUser[$comment->user_id]" size="text-sm" />
                                @endif
                            </div>
                            <p class="text-sm mt-1 whitespace-pre-line break-words">{{ $comment->body }}</p>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1 ml-1">{{ $comment->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @endforeach

            @if ($commentsTotal > $comments->count())
                <p class="text-xs text-base-content/60">Mostrando los {{ $comments->count() }} más recientes de {{ $commentsTotal }} comentarios.</p>
            @endif
        </div>
    @endif
</section>
