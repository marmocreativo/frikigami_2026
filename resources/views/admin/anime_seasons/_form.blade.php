<div class="form-control">
    <label class="label"><span class="label-text">Anime</span></label>
    <select name="anime_id" class="select select-bordered w-full @error('anime_id') select-error @enderror" required>
        <option value="">Selecciona un anime</option>
        @php $selected = old('anime_id', $animeSeason->anime_id ?? null); @endphp
        @foreach ($animes as $anime)
            <option value="{{ $anime->id }}" @selected((string) $selected === (string) $anime->id)>
                {{ $anime->title }}
            </option>
        @endforeach
    </select>
    @error('anime_id')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="form-control">
        <label class="label"><span class="label-text">Número de temporada</span></label>
        <input type="number" name="number" value="{{ old('number', $animeSeason->number ?? '') }}"
               class="input input-bordered w-full @error('number') input-error @enderror" min="1" required>
        @error('number')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label"><span class="label-text">Año</span></label>
        <input type="number" name="year" value="{{ old('year', $animeSeason->year ?? '') }}"
               class="input input-bordered w-full @error('year') input-error @enderror"
               min="1900" max="2100" placeholder="Opcional">
        @error('year')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Título de la temporada</span></label>
    <input type="text" name="title" value="{{ old('title', $animeSeason->title ?? '') }}"
           class="input input-bordered w-full @error('title') input-error @enderror"
           placeholder="Opcional (ej. Shippuden, The Final Season)">
    @error('title')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($animeSeason) ? 'Actualizar Temporada' : 'Crear Temporada' }}
</button>