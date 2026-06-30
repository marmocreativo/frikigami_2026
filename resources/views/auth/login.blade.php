<x-layouts.guest title="Iniciar sesión">
    <h2 class="card-title text-2xl mb-4">Iniciar sesión</h2>

    @if (session('status'))
        <div class="alert alert-success mb-4">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">Correo electrónico</span>
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input input-bordered w-full @error('email') input-error @enderror"
                   required autofocus autocomplete="username">
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
                   required autocomplete="current-password">
            @error('password')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="label cursor-pointer gap-2">
                <input type="checkbox" name="remember" class="checkbox checkbox-sm">
                <span class="label-text">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link link-hover text-sm">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-full">
            Iniciar sesión
        </button>

        @if (Route::has('register'))
            <p class="text-center text-sm mt-4">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="link link-primary">Regístrate</a>
            </p>
        @endif
    </form>
</x-layouts.guest>