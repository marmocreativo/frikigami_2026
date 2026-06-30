<x-layouts.admin title="Dashboard">
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
        <a href="{{ route('admin.animes.index') }}" class="stat bg-base-100 shadow-sm rounded-box hover:shadow-md transition-shadow">
            <div class="stat-title">Animes</div>
            <div class="stat-value text-2xl">{{ \App\Models\Anime::count() }}</div>
        </a>
        <a href="{{ route('admin.news.index') }}" class="stat bg-base-100 shadow-sm rounded-box hover:shadow-md transition-shadow">
            <div class="stat-title">Noticias</div>
            <div class="stat-value text-2xl">{{ \App\Models\News::count() }}</div>
        </a>
        <a href="{{ route('admin.episodes.index') }}" class="stat bg-base-100 shadow-sm rounded-box hover:shadow-md transition-shadow">
            <div class="stat-title">Episodios</div>
            <div class="stat-value text-2xl">{{ \App\Models\Episode::count() }}</div>
        </a>
        <a href="{{ route('admin.people.index') }}" class="stat bg-base-100 shadow-sm rounded-box hover:shadow-md transition-shadow">
            <div class="stat-title">Personas</div>
            <div class="stat-value text-2xl">{{ \App\Models\Person::count() }}</div>
        </a>
        <a href="{{ route('admin.characters.index') }}" class="stat bg-base-100 shadow-sm rounded-box hover:shadow-md transition-shadow">
            <div class="stat-title">Personajes</div>
            <div class="stat-value text-2xl">{{ \App\Models\Character::count() }}</div>
        </a>
    </div>
</x-layouts.admin>