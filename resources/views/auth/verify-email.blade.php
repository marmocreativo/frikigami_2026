<x-layouts.guest title="Verificar correo">
    <h2 class="card-title text-2xl mb-4">Verifica tu correo</h2>

    <p class="text-sm text-base-content/70 mb-4">
        Gracias por registrarte. Antes de continuar, ¿podrías verificar tu correo haciendo clic en el enlace que te acabamos de enviar? Si no recibiste el correo, con gusto te enviamos otro.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">
            <span>Se ha enviado un nuevo enlace de verificación a tu correo.</span>
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                Reenviar correo de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-layouts.guest>