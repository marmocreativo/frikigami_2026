<x-layouts.admin :title="'Editar · ' . $anime->title">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar Anime</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.animes.update', $anime) }}" enctype="multipart/form-data" class="space-y-4">
                @method('PUT')
                @include('animes._form')
            </form>
        </div>
    </div>
</x-layouts.admin>