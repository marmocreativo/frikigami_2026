<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin · {{ $title ?? 'Frikigami' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'frikigami-light');
    </script>
</head>
<body class="min-h-screen bg-base-200">
    <div class="drawer lg:drawer-open">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle">

        {{-- Estado inicial del sidebar (se ejecuta antes del primer render para evitar parpadeo) --}}
        <script>
            (() => {
                const toggle = document.getElementById('admin-drawer');
                const desktop = window.matchMedia('(min-width: 1024px)');
                const KEY = 'admin-sidebar';

                if (desktop.matches) {
                    let saved = null;
                    try { saved = localStorage.getItem(KEY); } catch (e) {}
                    toggle.checked = saved !== 'closed';
                }

                toggle.addEventListener('change', () => {
                    if (desktop.matches) {
                        try { localStorage.setItem(KEY, toggle.checked ? 'open' : 'closed'); } catch (e) {}
                    }
                });

                // Al pasar a móvil, el menú debe quedar cerrado
                desktop.addEventListener('change', (e) => {
                    if (!e.matches) toggle.checked = false;
                });
            })();
        </script>

        {{-- Contenido --}}
        <div class="drawer-content flex flex-col p-4">
            <nav class="navbar bg-base-100 shadow-sm min-h-12 px-2">
                <label for="admin-drawer" aria-label="Alternar menú" class="btn btn-ghost btn-square btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>
                <span class="ml-2 text-sm font-semibold text-base-content/70">Panel de administración</span>
            </nav>

            <main class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-10 is-drawer-close:overflow-visible">
            <label for="admin-drawer" aria-label="Cerrar menú" class="drawer-overlay"></label>

            <aside class="flex min-h-full flex-col bg-base-100 shadow-sm is-drawer-close:w-16 is-drawer-open:w-64">

                {{-- Logo --}}
                <div class="flex items-center justify-center px-3 py-4">
                    <a href="{{ route('home') }}" aria-label="Frikigami">
                        <img src="{{ asset('images/logo.png') }}" alt="Frikigami" class="h-10 w-auto is-drawer-close:hidden">
                        <span class="hidden is-drawer-close:flex size-10 items-center justify-center rounded-box bg-primary text-primary-content text-lg font-bold">F</span>
                    </a>
                </div>

                {{-- Menú principal --}}
                <ul class="menu w-full grow gap-1 px-2">
                    <x-admin.nav-item route="admin.dashboard" label="Dashboard" icon="dashboard" />

                    <li class="my-1 h-px bg-base-300" role="separator"></li>

                    <x-admin.nav-item route="admin.news.index" label="Noticias" icon="news" />

                    <li class="my-1 h-px bg-base-300" role="separator"></li>

                    <x-admin.nav-item route="admin.animes.index" label="Animes" icon="anime" />
                    <x-admin.nav-item route="admin.anime-seasons.index" label="Temporadas" icon="stack" :child="true" />
                    <x-admin.nav-item route="admin.episodes.index" label="Episodios" icon="episode" :child="true" />
                    <x-admin.nav-item route="admin.characters.index" label="Personajes" icon="character" :child="true" />
                    <x-admin.nav-item route="admin.people.index" label="Personas (Cast)" icon="person" :child="true" />
                    <x-admin.nav-item route="admin.comments.index" label="Comentarios" icon="comment" :child="true" />

                    <li class="my-1 h-px bg-base-300" role="separator"></li>

                    <x-admin.nav-item route="admin.seasons.index" label="Temporadas" icon="calendar" />
                    <x-admin.nav-item route="admin.genres.index" label="Géneros" icon="tag" />

                    <li class="my-1 h-px bg-base-300" role="separator"></li>

                    <li class="menu-title is-drawer-close:hidden">Administración</li>
                    <x-admin.nav-item route="admin.users.index" label="Usuarios" icon="users" />

                    <li class="my-1 h-px bg-base-300" role="separator"></li>
                </ul>

                {{-- Cuenta --}}
                <ul class="menu w-full gap-1 px-2 pb-2">
                    <li>
                        <a href="{{ route('home') }}" data-tip="Ver sitio" class="is-drawer-close:tooltip is-drawer-close:tooltip-right">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            <span class="is-drawer-close:hidden">Ver sitio</span>
                        </a>
                    </li>
                    <li>
                        <button type="submit" form="logout-form" data-tip="Cerrar sesión" class="text-error is-drawer-close:tooltip is-drawer-close:tooltip-right">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                            </svg>
                            <span class="is-drawer-close:hidden">Cerrar sesión</span>
                        </button>
                    </li>
                </ul>

                <div class="border-t border-base-300 p-3 text-xs text-base-content/60 is-drawer-close:hidden">
                    {{ auth()->user()->name }}
                </div>
            </aside>
        </div>
    </div>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
</body>
</html>