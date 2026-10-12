<div class="flex gap-2 anime-select-row">
    <select name="animes[]" class="select select-bordered w-full">
        <option value="">Sin anime (noticia general)</option>
        @foreach ($animes as $anime)
            <option value="{{ $anime->id }}" @selected((string) $selectedId === (string) $anime->id)>
                {{ $anime->title }}
            </option>
        @endforeach
    </select>
    <button type="button" class="btn btn-ghost btn-square remove-anime" aria-label="Quitar anime">✕</button>
</div>