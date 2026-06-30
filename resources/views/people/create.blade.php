<x-layouts.app title="Nueva Persona">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Nueva Persona</h1>

        <form method="POST" action="{{ route('people.store') }}" enctype="multipart/form-data" class="space-y-4">
            @include('people._form')
        </form>
    </div>
</x-layouts.app>