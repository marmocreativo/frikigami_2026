@csrf

<div class="form-control">
    <label class="label" for="title">
        <span class="label-text">Título</span>
    </label>
    <input id="title" type="text" name="title" value="{{ old('title', $anime->title ?? '') }}"
           class="input input-bordered w-full @error('title') input-error @enderror"
           required autofocus>
    @error('title')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="form-control">
    <label class="label" for="synopsis">
        <span class="label-text">Sinopsis</span>
    </label>
    <textarea id="synopsis" name="synopsis" rows="4"
              class="textarea textarea-bordered w-full @error('synopsis') textarea-error @enderror">{{ old('synopsis', $anime->synopsis ?? '') }}</textarea>
    @error('synopsis')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="form-control">
    <label class="label" for="cover_image">
        <span class="label-text">Imagen de portada</span>
    </label>
    @if (isset($anime) && $anime->cover_image)
        <img src="{{ Storage::url($anime->cover_image) }}" alt="Portada actual" class="w-32 rounded mb-2">
    @endif
    <input id="cover_image" type="file" name="cover_image"
           class="file-input file-input-bordered w-full @error('cover_image') file-input-error @enderror">
    @error('cover_image')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="form-control">
        <label class="label" for="status">
            <span class="label-text">Estado</span>
        </label>
        <select id="status" name="status" class="select select-bordered w-full @error('status') select-error @enderror" required>
            @php
                $statuses = ['upcoming' => 'Próximamente', 'ongoing' => 'En emisión', 'finished' => 'Finalizado'];
                $currentStatus = old('status', $anime->status ?? 'upcoming');
            @endphp
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <span class="text-error text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-control">
        <label class="label" for="season_id">
            <span class="label-text">Temporada de estreno</span>
        </label>
        <select id="season_id" name="season_id" class="select select-bordered w-full @error('season_id') select-error @enderror">
            <option value="">Sin definir</option>
            @php $currentSeason = old('season_id', $anime->season_id ?? null); @endphp
            @foreach ($seasons as $season)
                <option value="{{ $season->id }}" @selected((string) $currentSeason === (string) $season->id)>
                    {{ $season->label }}
                </option>
            @endforeach
        </select>
        @error('season_id')
            <span class="text-error text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-control">
    <label class="label">
        <span class="label-text">Géneros</span>
    </label>
    @php
        $selectedGenres = old('genres', isset($anime) ? $anime->genres->pluck('id')->toArray() : []);
    @endphp
    <div class="flex flex-wrap gap-3">
        @foreach ($genres as $genre)
            <label class="label cursor-pointer gap-2 border rounded-lg px-3 py-1">
                <input type="checkbox" name="genres[]" value="{{ $genre->id }}" class="checkbox checkbox-sm"
                       @checked(in_array($genre->id, $selectedGenres))>
                <span class="label-text">{{ $genre->name }}</span>
            </label>
        @endforeach
    </div>
    @error('genres')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($anime) ? 'Actualizar Anime' : 'Crear Anime' }}
</button>