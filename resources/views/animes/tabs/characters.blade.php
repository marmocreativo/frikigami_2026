@if ($anime->characters->isEmpty())
    <p class="text-base-content/60">No hay personajes registrados para este anime.</p>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
        @foreach ($anime->characters as $character)
            <a href="{{ route('characters.show', $character) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                <figure class="pt-4">
                    @if ($character->photo)
                        <img src="{{ Storage::url($character->photo) }}" alt="{{ $character->name }}" class="w-20 h-20 rounded-full object-cover">
                    @else
                        <div class="w-20 h-20 rounded-full bg-base-300 flex items-center justify-center text-2xl text-base-content/40">
                            {{ strtoupper(substr($character->name, 0, 1)) }}
                        </div>
                    @endif
                </figure>
                <div class="card-body items-center text-center p-3">
                    <p class="font-medium text-sm">{{ $character->name }}</p>
                    @if ($character->voiceActors->isNotEmpty())
                        <p class="text-xs text-base-content/60">{{ $character->voiceActors->first()->name }}</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif