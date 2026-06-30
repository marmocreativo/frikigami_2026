<x-layouts.guest title="Restablecer contraseña">
    <h2 class="card-title text-2xl mb-4">Restablecer contraseña</h2>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">Correo electrónico</span>
            </label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                   class="input input-bordered w-full @error('email') input-error @enderror"
                   required autofocus autocomplete="username">
            @error('email')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text">Nueva contraseña</span>
            </label>
            <input id="password" type="password" name="password"
                   class="input input-bordered w-full @error('password') input-error @enderror"
                   required autocomplete="new-password">
            @error('password')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-control">
            <label class="label" for="password_confirmation">
                <span class="label-text">Confirmar nueva contraseña</span>
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="input input-bordered w-full"
                   required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary w-full">
            Restablecer contraseña
        </button>
    </form>
</x-layouts.guest>