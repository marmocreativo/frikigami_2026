<x-layouts.guest title="Crear cuenta">
    <h2 class="card-title text-2xl mb-4">Crear cuenta</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="form-control">
            <label class="label" for="name">
                <span class="label-text">Nombre</span>
            </label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="input input-bordered w-full @error('name') input-error @enderror"
                   required autofocus autocomplete="name">
            @error('name')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">Correo electrónico</span>
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input input-bordered w-full @error('email') input-error @enderror"
                   required autocomplete="username">
            @error('email')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text">Contraseña</span>
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
                <span class="label-text">Confirmar contraseña</span>
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="input input-bordered w-full @error('password_confirmation') input-error @enderror"
                   required autocomplete="new-password">
            @error('password_confirmation')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full">
            Registrarme
        </button>

        <p class="text-center text-sm mt-4">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="link link-primary">Inicia sesión</a>
        </p>
    </form>
</x-layouts.guest>