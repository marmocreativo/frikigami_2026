<footer class="bg-neutral text-neutral-content mt-20">
    <x-container>
        <div class="py-12 grid grid-cols-1 md:grid-cols-4 gap-8">

            {{-- Logo + descripción --}}
            <div class="md:col-span-2">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('images/logo.png') }}" alt="Frikigami" class="h-16 w-auto">
                </a>
                <p class="text-sm text-neutral-content/70 mt-4 max-w-sm">
                    Noticias, opiniones y catálogo de anime, todo en un solo lugar. Únete a la comunidad de fans más grande.
                </p>
            </div>

            {{-- Navegación --}}
            <div>
                <h3 class="font-bold text-sm uppercase tracking-wide mb-4">Navegación</h3>
                <ul class="space-y-2 text-sm text-neutral-content/70">
                    <li><a href="{{ route('home') }}" class="hover:text-primary transition-colors">Home</a></li>
                    <li><a href="{{ route('animes.index') }}" class="hover:text-primary transition-colors">Animes</a></li>
                    <li><a href="{{ route('news.index') }}" class="hover:text-primary transition-colors">Noticias</a></li>
                    <li><a href="{{ route('polls.index') }}" class="hover:text-primary transition-colors">Encuestas</a></li>
                    <li><a href="{{ route('shop.index') }}" class="hover:text-primary transition-colors">Tienda</a></li>
                </ul>
            </div>

            {{-- Comunidad / redes --}}
            <div>
                <h3 class="font-bold text-sm uppercase tracking-wide mb-4">Comunidad</h3>
                <ul class="space-y-2 text-sm text-neutral-content/70">
                    <li><a href="#" class="hover:text-primary transition-colors">Discord</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Twitter / X</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Instagram</a></li>
                </ul>
            </div>

        </div>

        <div class="border-t border-neutral-content/10 py-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-neutral-content/60">
                &copy; {{ now()->year }} Frikigami. Todos los derechos reservados.
            </p>
            <p class="text-xs text-neutral-content/60">
                Hecho con ❤️ para la comunidad otaku.
            </p>
        </div>
    </x-container>
</footer>