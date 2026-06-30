<x-layouts.admin :title="'Editar · ' . $genre->name">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.genres.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar Género</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.genres.update', $genre) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('admin.genres._form')
            </form>
        </div>
    </div>
</x-layouts.admin>