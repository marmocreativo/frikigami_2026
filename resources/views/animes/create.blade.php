<x-layouts.app title="Nuevo Anime">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Nuevo Anime</h1>

        <form method="POST" action="{{ route('animes.store') }}" enctype="multipart/form-data" class="space-y-4">
            @include('animes._form')
        </form>
    </div>
</x-layouts.app>