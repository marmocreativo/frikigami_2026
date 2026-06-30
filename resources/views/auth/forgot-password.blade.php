<x-layouts.guest title="Recuperar contraseña">
    <h2 class="card-title text-2xl mb-4">Recuperar contraseña</h2>

    <p class="text-sm text-base-content/70 mb-4">
        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
    </p>

    @if (session('status'))
        <div class="alert alert-success mb-4">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">Correo electrónico</span>
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input input-bordered w-full @error('email') input-error @enderror"
                   required autofocus>
            @error('email')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full">
            Enviar enlace de recuperación
        </button>

        <p class="text-center text-sm mt-4">
            <a href="{{ route('login') }}" class="link link-hover">Volver a iniciar sesión</a>
        </p>
    </form>
</x-layouts.guest>