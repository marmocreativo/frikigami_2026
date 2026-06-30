<div class="space-y-6">
    @if ($anime->staff->isNotEmpty())
        <div>
            <h3 class="text-lg font-bold mb-3">Staff de producción</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach ($anime->staff as $person)
                    <a href="{{ route('people.show', $person) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="card-body p-3 flex flex-row items-center gap-3">
                            @if ($person->photo)
                                <img src="{{ Storage::url($person->photo) }}" alt="{{ $person->name }}" class="w-12 h-12 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-neutral text-neutral-content flex items-center justify-center text-lg shrink-0">
                                    {{ strtoupper(substr($person->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-sm truncate">{{ $person->name }}</p>
                                <p class="text-xs text-base-content/60">{{ $person->pivot->role }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($anime->characters->isNotEmpty())
        <div>
            <h3 class="text-lg font-bold mb-3">Actores de voz</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach ($anime->characters as $character)
                    @foreach ($character->voiceActors as $person)
                        <div class="card bg-base-100 shadow-sm">
                            <div class="card-body p-3">
                                <div class="flex items-center gap-2 mb-2">
                                    @if ($character->photo)
                                        <img src="{{ Storage::url($character->photo) }}" alt="{{ $character->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-base-300 flex items-center justify-center text-sm">
                                            {{ strtoupper(substr($character->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <a href="{{ route('characters.show', $character) }}" class="text-sm font-medium hover:underline truncate">{{ $character->name }}</a>
                                </div>
                                <div class="flex items-center gap-2 pl-2 border-l-2 border-base-300">
                                    @if ($person->photo)
                                        <img src="{{ Storage::url($person->photo) }}" alt="{{ $person->name }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-neutral text-neutral-content flex items-center justify-center text-xs">
                                            {{ strtoupper(substr($person->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <a href="{{ route('people.show', $person) }}" class="text-xs hover:underline truncate">{{ $person->name }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>

            @if ($anime->characters->every(fn($c) => $c->voiceActors->isEmpty()))
                <p class="text-base-content/60">No hay actores de voz registrados aún.</p>
            @endif
        </div>
    @endif

    @if ($anime->staff->isEmpty() && $anime->characters->every(fn($c) => $c->voiceActors->isEmpty()))
        <p class="text-base-content/60">No hay información de cast disponible.</p>
    @endif
</div>