<x-layouts.admin :title="'Editar · ' . $anime->title">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar Anime</h1>
    </div>

    @include('admin.partials.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 lg:grid-rows-[auto_1fr] gap-6 items-start">
        <form method="POST" action="{{ route('admin.animes.update', $anime) }}" enctype="multipart/form-data" class="contents">
            @method('PUT')
            @include('animes._form')
        </form>

        <div class="lg:col-start-3">
            <x-admin.anime-translate-panel :anime="$anime" />
        </div>
    </div>
</x-layouts.admin>