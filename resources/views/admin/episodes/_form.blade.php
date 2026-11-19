<div class="form-control">
    <label class="label"><span class="label-text">Temporada de la serie</span></label>
    <select name="anime_season_id" class="select select-bordered w-full @error('anime_season_id') select-error @enderror" required>
        <option value="">Selecciona una temporada</option>
        @php $selected = old('anime_season_id', $episode->anime_season_id ?? $selectedAnimeSeasonId ?? null); @endphp
        @foreach ($animeSeasons as $animeSeason)
            <option value="{{ $animeSeason->id }}" @selected((string) $selected === (string) $animeSeason->id)>
                {{ $animeSeason->anime->title }} — {{ $animeSeason->label }}
            </option>
        @endforeach
    </select>
    @error('anime_season_id')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="form-control">
        <label class="label"><span class="label-text">Número de episodio</span></label>
        <input type="number" name="number" value="{{ old('number', $episode->number ?? $nextNumber ?? '') }}"
               class="input input-bordered w-full @error('number') input-error @enderror" min="1" required>
        @error('number')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label"><span class="label-text">Fecha de emisión</span></label>
        <input type="date" name="air_date" value="{{ old('air_date', isset($episode) ? $episode->air_date?->format('Y-m-d') : '') }}"
               class="input input-bordered w-full @error('air_date') input-error @enderror">
        @error('air_date')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Título del episodio</span></label>
    <input type="text" name="title" value="{{ old('title', $episode->title ?? '') }}"
           class="input input-bordered w-full @error('title') input-error @enderror"
           placeholder="Opcional">
    @error('title')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Imagen previa</span></label>
    @if (isset($episode) && $episode->thumbnail)
        <img src="{{ $episode->thumbnail_url }}" alt="Imagen actual" class="w-full max-w-sm aspect-video object-cover rounded mb-2">
    @endif
    <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp"
           class="file-input file-input-bordered w-full @error('thumbnail') file-input-error @enderror">
    <p class="text-xs text-base-content/60 mt-1">
        Horizontal (16:9). JPG, PNG o WebP, máximo 4 MB.
        @if (isset($episode) && $episode->thumbnail) Déjalo vacío para conservar la actual. @endif
    </p>
    @error('thumbnail')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    @if (isset($episode) && $episode->thumbnail)
        <label class="label cursor-pointer justify-start gap-2 mt-1">
            <input type="checkbox" name="remove_thumbnail" value="1" class="checkbox checkbox-sm">
            <span class="label-text">Quitar la imagen actual</span>
        </label>
    @endif
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Resumen</span></label>
    <textarea name="synopsis" rows="4"
              class="textarea textarea-bordered w-full @error('synopsis') textarea-error @enderror"
              placeholder="Opcional">{{ old('synopsis', $episode->synopsis ?? '') }}</textarea>
    @error('synopsis')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($episode) ? 'Actualizar Episodio' : 'Crear Episodio' }}
</button>