<x-layouts.app title="Editar Anime">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Editar Anime</h1>

        <form method="POST" action="{{ route('animes.update', $anime) }}" enctype="multipart/form-data" class="space-y-4">
            @method('PUT')
            @include('animes._form')
        </form>
    </div>
</x-layouts.app>