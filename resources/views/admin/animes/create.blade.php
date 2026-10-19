<x-layouts.admin title="Nuevo Anime">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Nuevo Anime</h1>
    </div>

    @include('admin.partials.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <form method="POST" action="{{ route('admin.animes.store') }}" enctype="multipart/form-data" class="contents">
            @include('animes._form')
        </form>
    </div>
</x-layouts.admin>