<x-layouts.admin :title="'Editar · ' . $anime->title">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar Anime</h1>
    </div>

    @include('admin.partials.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 lg:grid-rows-[auto_1fr] gap-6 items-start">
        <form method="POST" action="{{ route('admin.animes.update', $anime) }}" enctype="multipart/form-data" class="contents">
            @method('PUT')
            @include('admin.animes._form')
        </form>

        <div class="lg:col-start-3">
            <x-admin.anime-translate-panel :anime="$anime" />

            <div class="card bg-base-100 shadow-sm mt-6">
                <div class="card-body">
                    <h2 class="card-title text-base">Mover como temporada de otro anime</h2>
                    <p class="text-sm text-base-content/60">
                        Si este anime es en realidad una continuación (ej. «Season 2»), pásalo como temporada
                        de su anime padre. Se conservan sus temporadas, episodios, personajes y reparto,
                        y este anime se elimina.
                    </p>
                    <form method="POST" action="{{ route('admin.animes.mover', $anime) }}" class="space-y-3 mt-2"
                          onsubmit="return confirm('Este anime se eliminará y pasará a ser temporada del anime elegido. ¿Continuar?');">
                        @csrf
                        <select name="target_id" class="select select-bordered w-full @error('target_id') select-error @enderror" required>
                            <option value="">Anime padre…</option>
                            @foreach ($otros as $otro)
                                <option value="{{ $otro->id }}">{{ $otro->title }}</option>
                            @endforeach
                        </select>
                        @error('target_id')<span class="text-error text-sm">{{ $message }}</span>@enderror
                        <button type="submit" class="btn btn-outline btn-sm w-full">Mover como temporada</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>