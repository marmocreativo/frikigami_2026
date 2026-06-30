<x-layouts.app title="Editar Persona">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Editar Persona</h1>

        <form method="POST" action="{{ route('people.update', $person) }}" enctype="multipart/form-data" class="space-y-4">
            @method('PUT')
            @include('people._form')
        </form>
    </div>
</x-layouts.app>