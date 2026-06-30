<x-layouts.app title="Personas">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Seiyuus y Staff</h1>
        @auth
            <a href="{{ route('people.create') }}" class="btn btn-primary">+ Agregar Persona</a>
        @endauth
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($people->isEmpty())
        <p class="text-base-content/60">Todavía no hay personas registradas.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
            @foreach ($people as $person)
                <a href="{{ route('people.show', $person) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                    <figure class="pt-4">
                        @if ($person->photo)
                            <img src="{{ Storage::url($person->photo) }}" alt="{{ $person->name }}" class="h-24 w-24 rounded-full object-cover">
                        @else
                            <div class="h-24 w-24 rounded-full bg-neutral text-neutral-content flex items-center justify-center text-2xl">
                                {{ strtoupper(substr($person->name, 0, 1)) }}
                            </div>
                        @endif
                    </figure>
                    <div class="card-body items-center text-center p-3">
                        <h2 class="card-title text-sm">{{ $person->name }}</h2>
                        <div class="flex gap-1">
                            @if ($person->is_voice_actor)
                                <span class="badge badge-xs badge-primary">Seiyuu</span>
                            @endif
                            @if ($person->is_staff)
                                <span class="badge badge-xs badge-secondary">Staff</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $people->links() }}
        </div>
    @endif
</x-layouts.app>