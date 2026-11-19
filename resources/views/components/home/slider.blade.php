@props(['slides'])

{{-- Carrusel del inicio: ancho completo, 720px mínimo. Sin dependencias (JS al final). --}}
<section class="relative w-full min-h-[720px] overflow-hidden" data-slider aria-roledescription="carrusel">
    @foreach ($slides as $slide)
        <div data-slide
             class="absolute inset-0 transition-opacity duration-700 {{ $loop->first ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
             aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
            <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}"
                 class="absolute inset-0 h-full w-full object-cover"
                 @if (! $loop->first) loading="lazy" @endif>

            <div class="absolute inset-0 bg-gradient-to-t from-neutral/70 via-neutral/30 to-transparent"></div>

            <div class="relative z-10 h-full min-h-[720px] flex items-end">
                <div class="w-full max-w-8xl mx-auto px-4 md:px-8">
                    <div class="max-w-2xl pb-20">
                        @if ($slide->anime)
                            <div class="flex gap-2 mb-3">
                                <span class="badge badge-sm bg-base-100/90 text-base-content border-none p-2">{{ $slide->anime->title }}</span>
                            </div>
                        @endif

                        <h2 class="text-4xl md:text-5xl font-bold text-neutral-content leading-tight">{{ $slide->title }}</h2>

                        @if ($slide->target_url)
                            <a href="{{ $slide->target_url }}" class="btn btn-primary mt-6">Ver más</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if ($slides->count() > 1)
        <button type="button" data-prev aria-label="Anterior"
                class="btn btn-circle btn-ghost absolute left-3 top-1/2 -translate-y-1/2 z-20 bg-base-100/30 hover:bg-base-100/60 text-neutral-content">❮</button>
        <button type="button" data-next aria-label="Siguiente"
                class="btn btn-circle btn-ghost absolute right-3 top-1/2 -translate-y-1/2 z-20 bg-base-100/30 hover:bg-base-100/60 text-neutral-content">❯</button>

        <div class="absolute bottom-6 inset-x-0 z-20 flex justify-center gap-2">
            @foreach ($slides as $slide)
                <button type="button" data-dot="{{ $loop->index }}" aria-label="Ir al slide {{ $loop->iteration }}"
                        class="h-2 rounded-full transition-all {{ $loop->first ? 'w-8 bg-primary' : 'w-2 bg-neutral-content/60' }}"></button>
            @endforeach
        </div>

        <script>
            (() => {
                const root = document.currentScript.closest('[data-slider]');
                const slides = [...root.querySelectorAll('[data-slide]')];
                const dots = [...root.querySelectorAll('[data-dot]')];
                let current = 0, timer = null;

                const show = (i) => {
                    current = (i + slides.length) % slides.length;
                    slides.forEach((s, n) => {
                        const on = n === current;
                        s.classList.toggle('opacity-100', on);
                        s.classList.toggle('opacity-0', !on);
                        s.classList.toggle('pointer-events-none', !on);
                        s.setAttribute('aria-hidden', on ? 'false' : 'true');
                    });
                    dots.forEach((d, n) => {
                        d.classList.toggle('w-8', n === current);
                        d.classList.toggle('bg-primary', n === current);
                        d.classList.toggle('w-2', n !== current);
                        d.classList.toggle('bg-neutral-content/60', n !== current);
                    });
                };
                const start = () => { stop(); timer = setInterval(() => show(current + 1), 6000); };
                const stop = () => { if (timer) clearInterval(timer); timer = null; };

                root.querySelector('[data-prev]').addEventListener('click', () => { show(current - 1); start(); });
                root.querySelector('[data-next]').addEventListener('click', () => { show(current + 1); start(); });
                dots.forEach((d) => d.addEventListener('click', () => { show(+d.dataset.dot); start(); }));
                root.addEventListener('mouseenter', stop);
                root.addEventListener('mouseleave', start);
                if (!matchMedia('(prefers-reduced-motion: reduce)').matches) start();
            })();
        </script>
    @endif
</section>
