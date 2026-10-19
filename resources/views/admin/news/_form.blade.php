<div class="form-control">
    <label class="label"><span class="label-text">Título</span></label>
    <input type="text" name="title" value="{{ old('title', $news->title ?? '') }}"
           class="input input-bordered w-full @error('title') input-error @enderror" required autofocus>
    @error('title')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<div class="form-control">
    <div class="flex items-center justify-between">
        <label class="label" for="body"><span class="label-text">Contenido</span></label>
        <button type="button" id="ai-toggle" class="btn btn-secondary btn-xs">✨ Expandir con IA</button>
    </div>

    <textarea id="body" name="body" rows="14"
              class="textarea textarea-bordered w-full @error('body') textarea-error @enderror" required>{{ old('body', $news->body ?? '') }}</textarea>
    <label class="label">
        <span class="label-text-alt text-base-content/60">
            Admite Markdown: <code>## Subtítulo</code>, <code>**negrita**</code> y listas con <code>-</code>.
        </span>
    </label>
    @error('body')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

{{-- Panel de IA --}}
<div id="ai-panel" class="card bg-base-200 border border-base-300 hidden">
    <div class="card-body p-4 space-y-3">
        <h3 class="font-semibold">Expandir con IA</h3>
        <p class="text-sm text-base-content/70">
            Toma el título y el contenido que escribió el cliente y los convierte en una nota más completa y optimizada
            para buscadores, con subtítulos y metaetiquetas. <strong>Reemplaza el contenido del formulario</strong>
            (puedes restaurar el original) y no se guarda hasta que pulses el botón de abajo.
            Revisa siempre los datos antes de publicar.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <input type="text" id="ai-keyword" placeholder="Palabra clave principal (opcional)"
                   class="input input-bordered input-sm w-full sm:w-72">

            <select id="ai-length" class="select select-bordered select-sm w-full sm:w-52">
                <option value="short">Corta (~400 palabras)</option>
                <option value="medium" selected>Media (~700 palabras)</option>
                <option value="long">Larga (~1000 palabras)</option>
            </select>

            <button type="button" id="ai-run" class="btn btn-primary btn-sm">Generar</button>
            <button type="button" id="ai-restore" class="btn btn-ghost btn-sm hidden">Restaurar original</button>
        </div>

        <div id="ai-status" class="text-sm"></div>

        <div id="ai-title-suggestion" class="alert alert-info text-sm hidden">
            <span>Título sugerido: <strong id="ai-title-text"></strong></span>
            <button type="button" id="ai-title-use" class="btn btn-xs">Usar título</button>
        </div>

        <div id="ai-notes" class="alert alert-warning text-sm hidden">
            <div>
                <p class="font-semibold">Verifica antes de publicar:</p>
                <ul id="ai-notes-list" class="list-disc pl-5 mt-1 space-y-1"></ul>
            </div>
        </div>
    </div>
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

{{-- SEO --}}
<div class="card bg-base-200 border border-base-300">
    <div class="card-body p-4 space-y-3">
        <h3 class="font-semibold">SEO y metaetiquetas</h3>
        <p class="text-xs text-base-content/60">
            Si los dejas vacíos se usan el título de la nota y un extracto del contenido.
        </p>

        <div class="form-control">
            <div class="flex items-center justify-between">
                <label class="label" for="meta_title"><span class="label-text">Título SEO</span></label>
                <span id="meta-title-count" class="text-xs text-base-content/60"></span>
            </div>
            <input id="meta_title" type="text" name="meta_title" maxlength="70"
                   value="{{ old('meta_title', $news->meta_title ?? '') }}"
                   class="input input-bordered w-full @error('meta_title') input-error @enderror">
            @error('meta_title')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
        </div>

        <div class="form-control">
            <div class="flex items-center justify-between">
                <label class="label" for="meta_description"><span class="label-text">Descripción SEO</span></label>
                <span id="meta-description-count" class="text-xs text-base-content/60"></span>
            </div>
            <textarea id="meta_description" name="meta_description" rows="3" maxlength="200"
                      class="textarea textarea-bordered w-full @error('meta_description') textarea-error @enderror">{{ old('meta_description', $news->meta_description ?? '') }}</textarea>
            @error('meta_description')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
        </div>
    </div>
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

<button type="submit" class="btn btn-primary w-full">
    {{ isset($news) ? 'Actualizar Noticia' : 'Crear Noticia' }}
</button>

<script>
    (() => {
        // ── Selects de anime ─────────────────────────────
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

        // ── Contadores de metaetiquetas ──────────────────
        const metaTitle = document.getElementById('meta_title');
        const metaDescription = document.getElementById('meta_description');

        const counter = (input, output, ideal, max) => {
            const update = () => {
                const length = input.value.length;
                output.textContent = length + ' / ' + ideal + ' recomendados';
                output.classList.toggle('text-error', length > max);
            };
            input.addEventListener('input', update);
            update();
            return update;
        };

        const updateMetaTitle = counter(metaTitle, document.getElementById('meta-title-count'), 60, 70);
        const updateMetaDescription = counter(metaDescription, document.getElementById('meta-description-count'), 155, 200);

        // ── Expandir con IA ──────────────────────────────
        const panel = document.getElementById('ai-panel');
        const run = document.getElementById('ai-run');
        const restore = document.getElementById('ai-restore');
        const status = document.getElementById('ai-status');
        const notes = document.getElementById('ai-notes');
        const notesList = document.getElementById('ai-notes-list');
        const suggestion = document.getElementById('ai-title-suggestion');
        const suggestionText = document.getElementById('ai-title-text');
        const titleInput = document.querySelector('input[name="title"]');
        const bodyInput = document.getElementById('body');
        let original = null;

        document.getElementById('ai-toggle').addEventListener('click', () => panel.classList.toggle('hidden'));

        run.addEventListener('click', async () => {
            if (bodyInput.value.trim().length < 20) {
                status.textContent = 'Escribe primero el contenido de la nota (al menos unas líneas).';
                return;
            }

            const animeIds = [...document.querySelectorAll('select[name="animes[]"]')]
                .map((select) => select.value)
                .filter(Boolean);

            run.disabled = true;
            run.textContent = 'Generando…';
            status.textContent = 'Generando la nota; puede tardar entre 10 y 40 segundos.';
            notes.classList.add('hidden');
            suggestion.classList.add('hidden');

            try {
                const response = await fetch('{{ route('admin.news.expand') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        title: titleInput.value,
                        body: bodyInput.value,
                        keyword: document.getElementById('ai-keyword').value,
                        length: document.getElementById('ai-length').value,
                        anime_ids: animeIds,
                    }),
                });

                const payload = await response.json().catch(() => ({}));

                if (! response.ok) {
                    throw new Error(payload.message || 'No se pudo generar el contenido.');
                }

                // Guarda solo el primer original, para poder volver al texto del cliente
                original = original ?? {
                    body: bodyInput.value,
                    metaTitle: metaTitle.value,
                    metaDescription: metaDescription.value,
                };

                const data = payload.data;
                bodyInput.value = data.body;
                metaTitle.value = data.meta_title;
                metaDescription.value = data.meta_description;
                updateMetaTitle();
                updateMetaDescription();

                if (data.title && data.title !== titleInput.value) {
                    suggestionText.textContent = data.title;
                    suggestion.classList.remove('hidden');
                }

                notesList.replaceChildren(...data.review_notes.map((note) => {
                    const item = document.createElement('li');
                    item.textContent = note;
                    return item;
                }));
                notes.classList.toggle('hidden', data.review_notes.length === 0);

                restore.classList.remove('hidden');
                status.textContent = 'Listo. Revisa el contenido, las metaetiquetas y el título sugerido antes de guardar.';
            } catch (error) {
                status.textContent = error.message;
            } finally {
                run.disabled = false;
                run.textContent = 'Generar';
            }
        });

        restore.addEventListener('click', () => {
            if (! original) return;

            bodyInput.value = original.body;
            metaTitle.value = original.metaTitle;
            metaDescription.value = original.metaDescription;
            updateMetaTitle();
            updateMetaDescription();
            suggestion.classList.add('hidden');
            notes.classList.add('hidden');
            status.textContent = 'Se restauró el contenido original.';
        });

        document.getElementById('ai-title-use').addEventListener('click', () => {
            titleInput.value = suggestionText.textContent;
            suggestion.classList.add('hidden');
        });
    })();
</script>