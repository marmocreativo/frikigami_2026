<x-layouts.admin :title="'Editar · ' . $character->name">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.characters.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar Personaje</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.characters.update', $character) }}" enctype="multipart/form-data" class="space-y-4">
                @method('PUT')
                @include('characters._form')
            </form>
        </div>
    </div>
</x-layouts.admin>