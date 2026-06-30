<x-layouts.admin title="Nuevo Personaje">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.characters.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Nuevo Personaje</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.characters.store') }}" enctype="multipart/form-data" class="space-y-4">
                @include('characters._form')
            </form>
        </div>
    </div>
</x-layouts.admin>