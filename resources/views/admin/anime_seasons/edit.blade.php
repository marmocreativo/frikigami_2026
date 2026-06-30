<x-layouts.admin title="Editar Temporada">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.anime-seasons.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar Temporada de serie</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.anime-seasons.update', $animeSeason) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('admin.anime_seasons._form')
            </form>
        </div>
    </div>
</x-layouts.admin>