<x-layouts.admin :title="'Editar · ' . $news->title">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.news.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar Noticia</h1>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.news.update', $news) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                @include('admin.news._form')
            </form>
        </div>
    </div>
</x-layouts.admin>