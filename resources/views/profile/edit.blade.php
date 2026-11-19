<x-layouts.app title="Mi perfil">
    <x-container class="pt-28 max-w-2xl">

        <h1 class="text-3xl font-bold mb-6">Mi perfil</h1>

        @if (session('status'))
            <div class="alert alert-success mb-4"><span>{{ session('status') }}</span></div>
        @endif

        {{-- Actividad --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-base-100 rounded-box p-5">
                <p class="text-sm text-base-content/60">Comentarios</p>
                <p class="text-3xl font-bold mt-1">{{ $commentsCount }}</p>
            </div>
            <div class="bg-base-100 rounded-box p-5">
                <p class="text-sm text-base-content/60">Calificaciones</p>
                <p class="text-3xl font-bold mt-1">{{ $ratingsCount }}</p>
            </div>
        </div>

        <div class="bg-base-100 rounded-box p-6">
            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Foto --}}
                <div class="flex items-center gap-5">
                    <div class="avatar {{ $user->avatar_url ? '' : 'avatar-placeholder' }}">
                        <div class="w-24 rounded-full {{ $user->avatar_url ? '' : 'bg-neutral text-neutral-content' }}">
                            @if ($user->avatar_url)
                                <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Foto de perfil" referrerpolicy="no-referrer">
                            @else
                                <span id="avatar-initials" class="text-3xl">{{ $user->initials }}</span>
                                <img id="avatar-preview" class="hidden" alt="Foto de perfil">
                            @endif
                        </div>
                    </div>

                    <div class="flex-1">
                        <label class="label" for="avatar"><span class="label-text">Foto de perfil</span></label>
                        <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                               class="file-input file-input-bordered file-input-sm w-full @error('avatar') file-input-error @enderror">
                        <p class="text-xs text-base-content/60 mt-1">JPG, PNG o WebP. Máximo 2 MB.</p>
                        @error('avatar')
                            <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="name"><span class="label-text">Nombre</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="input input-bordered w-full @error('name') input-error @enderror" autocomplete="given-name">
                        @error('name')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="last_name"><span class="label-text">Apellidos</span></label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                               class="input input-bordered w-full @error('last_name') input-error @enderror" autocomplete="family-name">
                        @error('last_name')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-control">
                    <label class="label" for="email"><span class="label-text">Correo electrónico</span></label>
                    <input id="email" type="email" value="{{ $user->email }}" disabled class="input input-bordered w-full">
                    @if ($user->google_id)
                        <p class="text-xs text-base-content/60 mt-1">Cuenta vinculada con Google.</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 justify-between">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>

            @if ($user->avatar || $user->google_avatar)
                <form method="POST" action="{{ route('perfil.foto.destroy') }}" class="mt-4"
                      onsubmit="return confirm('¿Quitar tu foto de perfil?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm text-error">Quitar foto</button>
                </form>
            @endif
        </div>
    </x-container>

    <script>
        // Vista previa de la foto antes de guardar
        document.getElementById('avatar')?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const img = document.getElementById('avatar-preview');
            img.src = URL.createObjectURL(file);
            img.classList.remove('hidden');
            document.getElementById('avatar-initials')?.classList.add('hidden');
        });
    </script>
</x-layouts.app>
