<x-layouts.admin title="Nueva Temporada">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.anime-seasons.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Nueva Temporada de serie</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.anime-seasons.store') }}" class="space-y-4">
                @csrf
                @include('admin.anime_seasons._form')
            </form>
        </div>
    </div>
</x-layouts.admin>