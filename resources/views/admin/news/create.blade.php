<x-layouts.admin title="Nueva Noticia">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.news.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Nueva Noticia</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @include('admin.news._form')
            </form>
        </div>
    </div>
</x-layouts.admin>