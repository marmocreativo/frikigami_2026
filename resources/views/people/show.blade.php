<x-layouts.app :title="$person->name">
    <div class="max-w-3xl mx-auto">
        @if (session('status'))
            <div class="alert alert-success mb-4">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row gap-6">
            @if ($person->photo)
                <img src="{{ Storage::url($person->photo) }}" alt="{{ $person->name }}" class="w-48 h-48 rounded-box object-cover shadow">
            @else
                <div class="w-48 h-48 rounded-box bg-neutral text-neutral-content flex items-center justify-center text-5xl shadow">
                    {{ strtoupper(substr($person->name, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1">
                <h1 class="text-3xl font-bold">{{ $person->name }}</h1>

                <div class="flex gap-2 my-2">
                    @if ($person->is_voice_actor)
                        <span class="badge badge-primary">Seiyuu</span>
                    @endif
                    @if ($person->is_staff)
                        <span class="badge badge-secondary">Staff</span>
                    @endif
                </div>

                <p class="text-base-content/80">{{ $person->bio ?: 'Sin biografía disponible.' }}</p>

                @auth
                    <div class="flex gap-2 mt-4">
                        <a href="{{ route('people.edit', $person) }}" class="btn btn-sm btn-outline">Editar</a>
                        <form method="POST" action="{{ route('people.destroy', $person) }}" onsubmit="return confirm('¿Eliminar esta persona?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-error btn-outline">Eliminar</button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>

        @if ($person->is_voice_actor && $person->characters->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-3">Personajes interpretados</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ($person->characters as $character)
                        <a href="{{ route('characters.show', $character) }}" class="card bg-base-100 shadow-sm p-3 text-center">
                            <span class="text-sm font-medium">{{ $character->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($person->is_staff && $person->animes->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-3">Animes en los que ha trabajado</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ($person->animes as $anime)
                        <a href="{{ route('animes.show', $anime) }}" class="card bg-base-100 shadow-sm p-3 text-center">
                            <span class="text-sm font-medium">{{ $anime->title }}</span>
                            <span class="text-xs text-base-content/60">{{ $anime->pivot->role }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>