<x-layouts.app title="Personajes">
    <x-container class="pt-28">
        <h1 class="text-3xl font-bold mb-6">Personajes</h1>

        <form method="GET" action="{{ route('characters.index') }}" class="mb-6">
            <label class="input w-full">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar personajes..." />
            </label>
        </form>

        @if ($characters->isEmpty())
            <p class="text-base-content/60">No se encontraron personajes.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                @foreach ($characters as $character)
                    <a href="{{ route('characters.show', $character) }}"
                       class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                        <figure class="pt-4">
                            @if ($character->photo)
                                <img src="{{ Storage::url($character->photo) }}" alt="{{ $character->name }}"
                                     class="h-24 w-24 rounded-full object-cover">
                            @else
                                <div class="h-24 w-24 rounded-full bg-base-300 flex items-center justify-center text-2xl text-base-content/40">
                                    {{ strtoupper(substr($character->name, 0, 1)) }}
                                </div>
                            @endif
                        </figure>
                        <div class="card-body items-center text-center p-3">
                            <h2 class="card-title text-sm">{{ $character->name }}</h2>
                            @if ($character->animes->isNotEmpty())
                                <p class="text-xs text-base-content/60">{{ $character->animes->first()->title }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $characters->links() }}
            </div>
        @endif
    </x-container>
</x-layouts.app>