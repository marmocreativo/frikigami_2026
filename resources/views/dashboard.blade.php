<x-layouts.app title="Dashboard">
    <x-container class="pt-28">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">¡Bienvenido, {{ auth()->user()->name }}!</h1>
                <p class="text-base text-base-content/60 mt-1">Este es tu panel personal en Frikigami.</p>
            </div>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">Panel Admin</a>
            @endif
        </div>

        {{-- Stats rápidas --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-base-100 rounded-box p-6">
                <p class="text-sm text-base-content/60">Comentarios</p>
                <p class="text-3xl font-bold text-base-content mt-1">{{ auth()->user()->comments()->count() }}</p>
            </div>
            <div class="bg-base-100 rounded-box p-6">
                <p class="text-sm text-base-content/60">Noticias publicadas</p>
                <p class="text-3xl font-bold text-base-content mt-1">{{ auth()->user()->news()->count() }}</p>
            </div>
            <div class="bg-base-100 rounded-box p-6">
                <p class="text-sm text-base-content/60">Episodios agregados</p>
                <p class="text-3xl font-bold text-base-content mt-1">{{ auth()->user()->episodes()->count() }}</p>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="bg-base-100 rounded-box p-6 mb-8">
            <h2 class="text-xl font-bold text-base-content mb-4">Acciones rápidas</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('news.index') }}" class="flex items-center gap-3 p-4 rounded-field bg-primary/10 hover:bg-primary/20 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2zM9 8h6M9 12h6M9 16h3" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-sm text-base-content">Publicar noticia</p>
                        <p class="text-xs text-base-content/60">Comparte una novedad</p>
                    </div>
                </a>

                <a href="{{ route('animes.index') }}" class="flex items-center gap-3 p-4 rounded-field bg-secondary/10 hover:bg-secondary/20 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-secondary/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-sm text-base-content">Agregar episodio</p>
                        <p class="text-xs text-base-content/60">Registra un nuevo capítulo</p>
                    </div>
                </a>

                <a href="{{ route('home') }}" class="flex items-center gap-3 p-4 rounded-field bg-accent/10 hover:bg-accent/20 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-sm text-base-content">Explorar catálogo</p>
                        <p class="text-xs text-base-content/60">Descubre nuevos animes</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Aviso próximamente --}}
        <div class="bg-base-100 rounded-box p-6 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-warning/15 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm text-base-content/70">
                Próximamente vas a poder gestionar tus listas de seguimiento, puntos y logros directamente desde aquí.
            </p>
        </div>

    </x-container>
</x-layouts.app>