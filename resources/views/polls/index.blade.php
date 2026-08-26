<x-layouts.app title="Encuestas">
    <x-container class="pt-28">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Encuestas</h1>
        </div>

        <div class="bg-base-100 rounded-box p-12 md:p-20 text-center">
            <div class="w-20 h-20 rounded-full bg-primary/15 flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0a2 2 0 002 2h2a2 2 0 002-2v-3a2 2 0 00-2-2h-2a2 2 0 00-2 2v3z" />
                </svg>
            </div>

            <span class="badge bg-secondary/15 text-secondary border-none mb-3">Próximamente</span>

            <h2 class="text-2xl font-bold text-base-content mb-2">Las encuestas están en camino</h2>

            <p class="text-base text-base-content/60 max-w-md mx-auto">
                Muy pronto podrás votar por tus personajes, animes y temporadas favoritas, y ver los resultados de toda la comunidad en tiempo real.
            </p>

            <a href="{{ route('home') }}" class="btn btn-primary mt-8">Volver al inicio</a>
        </div>

        {{-- Preview de lo que vendrá --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <div class="bg-base-100 rounded-box p-6">
                <span class="badge bg-primary/15 text-primary border-none mb-3">Personajes</span>
                <p class="text-sm text-base-content/60">Vota por tu personaje favorito de la temporada.</p>
            </div>
            <div class="bg-base-100 rounded-box p-6">
                <span class="badge bg-secondary/15 text-secondary border-none mb-3">Animes</span>
                <p class="text-sm text-base-content/60">Elige el mejor anime entre los nominados por la comunidad.</p>
            </div>
            <div class="bg-base-100 rounded-box p-6">
                <span class="badge bg-accent/15 text-accent border-none mb-3">Temporada</span>
                <p class="text-sm text-base-content/60">Opina sobre lo mejor y peor de cada temporada en curso.</p>
            </div>
        </div>
    </x-container>
</x-layouts.app>