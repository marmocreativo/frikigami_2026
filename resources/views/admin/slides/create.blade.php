<x-layouts.admin title="Nuevo Slider">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.sliders.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Nuevo Slider</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sliders.store') }}" enctype="multipart/form-data" class="space-y-4">
                @include('admin.slides._form')
            </form>
        </div>
    </div>
</x-layouts.admin>
