<x-layouts.app title="Tienda">
    <x-container class="pt-28">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Tienda</h1>
        </div>

        <div class="bg-base-100 rounded-box p-12 md:p-20 text-center">
            <div class="w-20 h-20 rounded-full bg-secondary/15 flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>

            <span class="badge bg-primary/15 text-primary border-none mb-3">Próximamente</span>

            <h2 class="text-2xl font-bold text-base-content mb-2">La tienda está en construcción</h2>

            <p class="text-base text-base-content/60 max-w-md mx-auto">
                Muy pronto podrás conseguir merchandising, figuras y productos exclusivos de tus animes favoritos directamente desde Frikigami.
            </p>

            <a href="{{ route('home') }}" class="btn btn-primary mt-8">Volver al inicio</a>
        </div>

        {{-- Preview de lo que vendrá --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <div class="bg-base-100 rounded-box p-6">
                <span class="badge bg-primary/15 text-primary border-none mb-3">Figuras</span>
                <p class="text-sm text-base-content/60">Figuras de colección de tus personajes favoritos.</p>
            </div>
            <div class="bg-base-100 rounded-box p-6">
                <span class="badge bg-secondary/15 text-secondary border-none mb-3">Ropa</span>
                <p class="text-sm text-base-content/60">Playeras, hoodies y accesorios con diseños exclusivos.</p>
            </div>
            <div class="bg-base-100 rounded-box p-6">
                <span class="badge bg-accent/15 text-accent border-none mb-3">Manga</span>
                <p class="text-sm text-base-content/60">Volúmenes físicos y ediciones especiales.</p>
            </div>
        </div>
    </x-container>
</x-layouts.app>