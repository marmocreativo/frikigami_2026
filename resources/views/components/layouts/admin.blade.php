<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin · {{ $title ?? 'Frikigami' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="drawer lg:drawer-open">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle">

        <div class="drawer-content flex flex-col">
            {{-- Navbar top --}}
            <div class="navbar bg-base-100 shadow-sm lg:hidden">
                <label for="admin-drawer" class="btn btn-ghost drawer-button">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </label>
                <span class="text-lg font-bold ml-2">Admin · Frikigami</span>
            </div>

            {{-- Contenido --}}
            <main class="flex-1 p-6">
                <div class="max-w-6xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{-- Sidebar --}}
        <div class="drawer-side z-10">
            <label for="admin-drawer" class="drawer-overlay"></label>
            <aside class="bg-base-100 min-h-screen w-64 shadow-sm flex flex-col">
                <div class="p-4 border-b">
                    <a href="{{ route('home') }}" class="text-xl font-bold">Frikigami</a>
                    <p class="text-xs text-base-content/60 mt-1">Panel de administración</p>
                </div>

                <ul class="menu p-4 flex-1 gap-1">
                    <li class="menu-title">Contenido</li>
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.animes.index') }}"
                           class="{{ request()->routeIs('admin.animes.*') ? 'active' : '' }}">
                            Animes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.news.index') }}"
                           class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                            Noticias
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.episodes.index') }}"
                           class="{{ request()->routeIs('admin.episodes.*') ? 'active' : '' }}">
                            Episodios
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.anime-seasons.index') }}"
                           class="{{ request()->routeIs('admin.anime-seasons.*') ? 'active' : '' }}">
                            Temporadas de serie
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.genres.index') }}"
                           class="{{ request()->routeIs('admin.genres.*') ? 'active' : '' }}">
                            Géneros
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.seasons.index') }}"
                           class="{{ request()->routeIs('admin.seasons.*') ? 'active' : '' }}">
                            Temporadas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.comments.index') }}"
                           class="{{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
                            Comentarios
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.people.index') }}"
                           class="{{ request()->routeIs('admin.people.*') ? 'active' : '' }}">
                            Personas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.characters.index') }}"
                           class="{{ request()->routeIs('admin.characters.*') ? 'active' : '' }}">
                            Personajes
                        </a>
                    </li>
                    <li class="menu-title mt-4">Administración</li>
                    <li>
                        <a href="{{ route('admin.users.index') }}"
                           class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            Usuarios
                        </a>
                    </li>

                    <li class="menu-title mt-4">Cuenta</li>
                    <li>
                        <a href="{{ route('home') }}">← Ver sitio</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left text-error">Cerrar sesión</button>
                        </form>
                    </li>
                </ul>

                <div class="p-4 border-t text-xs text-base-content/60">
                    {{ auth()->user()->name }}
                </div>
            </aside>
        </div>
    </div>
</body>
</html>