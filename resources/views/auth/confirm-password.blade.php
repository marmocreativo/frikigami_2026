<x-layouts.guest title="Confirmar contraseña">
    <h2 class="card-title text-2xl mb-4">Confirmar contraseña</h2>

    <p class="text-sm text-base-content/70 mb-4">
        Esta es un área segura de la aplicación. Por favor confirma tu contraseña antes de continuar.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text">Contraseña</span>
            </label>
            <input id="password" type="password" name="password"
                   class="input input-bordered w-full @error('password') input-error @enderror"
                   required autofocus autocomplete="current-password">
            @error('password')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full">
            Confirmar
        </button>
    </form>
</x-layouts.guest>