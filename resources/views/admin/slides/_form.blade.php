@csrf

<div class="form-control">
    <label class="label" for="title"><span class="label-text">Título</span></label>
    <input id="title" type="text" name="title" value="{{ old('title', $slide->title ?? '') }}" required autofocus
           class="input input-bordered w-full @error('title') input-error @enderror">
    @error('title')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label" for="image"><span class="label-text">Imagen</span></label>
    @if (isset($slide) && $slide->image)
        <img src="{{ $slide->image_url }}" alt="Imagen actual" class="w-full max-w-md aspect-[8/3] object-cover rounded mb-2">
    @endif
    <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
           class="file-input file-input-bordered w-full @error('image') file-input-error @enderror"
           @required(! isset($slide))>
    <p class="text-xs text-base-content/60 mt-1">
        Recomendado: horizontal, 1920×720 px o más. JPG, PNG o WebP, máximo 4 MB.
        @isset($slide) Déjalo vacío para conservar la actual. @endisset
    </p>
    @error('image')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label" for="anime_id"><span class="label-text">Anime relacionado</span></label>
    <select id="anime_id" name="anime_id" class="select select-bordered w-full @error('anime_id') select-error @enderror">
        <option value="">Ninguno</option>
        @foreach ($animes as $anime)
            <option value="{{ $anime->id }}" @selected((string) old('anime_id', $slide->anime_id ?? '') === (string) $anime->id)>
                {{ $anime->title }}
            </option>
        @endforeach
    </select>
    @error('anime_id')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <label class="label" for="link"><span class="label-text">Link</span></label>
    <input id="link" type="text" name="link" value="{{ old('link', $slide->link ?? '') }}"
           placeholder="https://... o /noticias/mi-noticia"
           class="input input-bordered w-full @error('link') input-error @enderror">
    <p class="text-xs text-base-content/60 mt-1">Si lo dejas vacío y eliges un anime, el botón lleva a la ficha de ese anime.</p>
    @error('link')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
    <div class="form-control">
        <label class="label" for="position"><span class="label-text">Orden</span></label>
        <input id="position" type="number" name="position" min="0" value="{{ old('position', $slide->position ?? '') }}"
               placeholder="Vacío = al final"
               class="input input-bordered w-full @error('position') input-error @enderror">
        @error('position')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </div>

    <label class="label cursor-pointer justify-start gap-3">
        <input type="checkbox" name="is_active" value="1" class="checkbox"
               @checked(old('is_active', $slide->is_active ?? true))>
        <span class="label-text">Mostrar en el inicio</span>
    </label>
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($slide) ? 'Actualizar Slider' : 'Crear Slider' }}
</button>
