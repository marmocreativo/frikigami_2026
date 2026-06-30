<x-layouts.admin title="Nuevo Género">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.genres.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Nuevo Género</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.genres.store') }}" class="space-y-4">
                @csrf
                @include('admin.genres._form')
            </form>
        </div>
    </div>
</x-layouts.admin>