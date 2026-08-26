<div class="navbar fixed top-5 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2.5rem)] max-w-8xl bg-base-100/80 backdrop-blur-md shadow-lg rounded-box px-4">
    <div class="navbar-start">
        <div class="dropdown lg:hidden">
            <div tabindex="0" role="button" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-10 mt-3 w-52 p-2 shadow">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('animes.index') }}">Animes</a></li>
                <li><a href="{{ route('news.index') }}">Noticias</a></li>
                <li><a href="{{ route('polls.index') }}">Encuestas</a></li>
                <li><a href="{{ route('shop.index') }}">Tienda</a></li>
            </ul>
        </div>
        <a href="{{ route('home') }}" class="btn btn-ghost">
            <img src="{{ asset('images/logo.png') }}" alt="Frikigami" class="h-24 w-auto">
        </a>
    </div>

    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal px-1">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('animes.index') }}">Animes</a></li>
            <li><a href="{{ route('news.index') }}">Noticias</a></li>
            <li><a href="{{ route('polls.index') }}">Encuestas</a></li>
            <li><a href="{{ route('shop.index') }}">Tienda</a></li>
        </ul>
    </div>

    <div class="navbar-end gap-2">
        <form action="{{ route('animes.index') }}" method="GET" class="hidden md:block">
            <input type="text" name="q" placeholder="Buscar anime..." class="input input-bordered input-sm w-48">
        </form>

        <x-theme-toggle />

        @auth
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar placeholder">
                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                        <span class="text-xs">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-10 mt-3 w-52 p-2 shadow">
                    <li class="menu-title">{{ auth()->user()->name }}</li>
                    <li><a href="{{ route('dashboard') }}">Mi perfil</a></li>
                    @if (auth()->user()->isAdmin())
                        <li><a href="{{ route('admin.dashboard') }}" class="text-primary">Panel Admin</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left">Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Iniciar sesión</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Registrarme</a>
        @endauth
    </div>
</div>