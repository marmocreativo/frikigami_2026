@php
    $labels = \App\Services\AnimeContentTranslator::TYPES;
    $total = collect($summary)->sum('count');
@endphp

<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <h2 class="card-title text-base">Traducir contenido al español</h2>
        <p class="text-sm text-base-content/60">
            Traduce lo que esté en inglés de este anime. Se traduce lo que está <strong>guardado</strong>:
            si editaste el formulario de arriba, guarda los cambios antes. Lo que ya está en español no se toca.
        </p>

        @if ($errors->has('types') || $errors->has('types.*'))
            <div class="alert alert-error">
                <span>{{ $errors->first('types') ?: $errors->first('types.*') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.animes.translate', $anime) }}" class="space-y-3 mt-2">
            @csrf

            @foreach ($labels as $type => $label)
                @php $count = $summary[$type]['count']; @endphp
                <label class="flex items-center gap-3 {{ $count ? 'cursor-pointer' : 'opacity-50' }}">
                    <input type="checkbox" name="types[]" value="{{ $type }}" class="checkbox checkbox-sm"
                           @checked($count > 0) @disabled($count === 0)>
                    <span class="text-sm">{{ $label }}</span>
                    <span class="badge badge-ghost badge-sm">
                        @if ($count)
                            {{ $count }} en inglés · {{ number_format($summary[$type]['chars']) }} caracteres
                        @else
                            Nada pendiente
                        @endif
                    </span>
                </label>
            @endforeach

            <button type="submit" class="btn btn-primary btn-sm" @disabled($total === 0)
                    onclick="return confirm('¿Traducir el contenido seleccionado? Usa el proveedor de IA configurado y puede tardar unos segundos.');">
                Traducir contenido
            </button>
        </form>
    </div>
</div>