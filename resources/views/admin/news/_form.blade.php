<div class="form-control">
    <label class="label"><span class="label-text">Título</span></label>
    <input type="text" name="title" value="{{ old('title', $news->title ?? '') }}"
           class="input input-bordered w-full @error('title') input-error @enderror" required autofocus>
    @error('title')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Contenido</span></label>
    <textarea name="body" rows="8"
              class="textarea textarea-bordered w-full @error('body') textarea-error @enderror" required>{{ old('body', $news->body ?? '') }}</textarea>
    @error('body')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Imagen de portada</span></label>
    @if (isset($news) && $news->cover_image)
        <img src="{{ Storage::url($news->cover_image) }}" alt="Portada actual" class="w-32 rounded mb-2">
    @endif
    <input type="file" name="cover_image"
           class="file-input file-input-bordered w-full @error('cover_image') file-input-error @enderror">
    @error('cover_image')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Fecha de publicación</span></label>
    <input type="datetime-local" name="published_at"
           value="{{ old('published_at', isset($news) && $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '') }}"
           class="input input-bordered w-full @error('published_at') input-error @enderror">
    <label class="label"><span class="label-text-alt text-base-content/60">Déjalo vacío para guardar como borrador.</span></label>
    @error('published_at')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label"><span class="label-text">Animes relacionados</span></label>
    @php $selectedAnimes = old('animes', isset($news) ? $news->animes->pluck('id')->toArray() : []); @endphp
    <div class="flex flex-wrap gap-3">
        @foreach ($animes as $anime)
            <label class="label cursor-pointer gap-2 border rounded-lg px-3 py-1">
                <input type="checkbox" name="animes[]" value="{{ $anime->id }}" class="checkbox checkbox-sm"
                       @checked(in_array($anime->id, $selectedAnimes))>
                <span class="label-text">{{ $anime->title }}</span>
            </label>
        @endforeach
    </div>
    @error('animes')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($news) ? 'Actualizar Noticia' : 'Crear Noticia' }}
</button>