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
    <label class="label"><span class="label-text">Anime relacionado</span></label>

    @php
        $selectedAnimes = old('animes', isset($news) ? $news->animes->pluck('id')->all() : ($selectedAnimeIds ?? []));
        $selectedAnimes = array_values(array_filter($selectedAnimes));

        if ($selectedAnimes === []) {
            $selectedAnimes = [''];
        }
    @endphp

    @if (! empty($returnAnime))
        <input type="hidden" name="return_anime" value="{{ $returnAnime }}">
    @endif

    <div id="anime-selects" class="space-y-2">
        @foreach ($selectedAnimes as $selectedId)
            @include('admin.news._anime-select', ['selectedId' => $selectedId])
        @endforeach
    </div>

    <template id="anime-select-template">
        @include('admin.news._anime-select', ['selectedId' => ''])
    </template>

    <button type="button" id="add-anime" class="btn btn-ghost btn-sm mt-2 self-start">+ Agregar otro anime</button>

    @error('animes')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    @error('animes.*')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<script>
    (() => {
        const container = document.getElementById('anime-selects');
        const template = document.getElementById('anime-select-template');

        document.getElementById('add-anime').addEventListener('click', () => {
            container.appendChild(template.content.cloneNode(true));
        });

        container.addEventListener('click', (event) => {
            const button = event.target.closest('.remove-anime');
            if (! button) return;

            const rows = container.querySelectorAll('.anime-select-row');

            if (rows.length > 1) {
                button.closest('.anime-select-row').remove();
            } else {
                rows[0].querySelector('select').value = '';
            }
        });
    })();
</script>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($news) ? 'Actualizar Noticia' : 'Crear Noticia' }}
</button>