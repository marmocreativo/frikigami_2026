<div class="space-y-6">

    {{-- Agregar personaje --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base mb-3">Agregar personaje</h2>

            <form method="POST" action="{{ route('admin.animes.characters.attach', $anime) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                @csrf
                <div class="form-control">
                    <label class="label"><span class="label-text">Personaje</span></label>
                    <select name="character_id" class="select select-bordered select-sm w-full" required>
                        <option value="">Selecciona un personaje</option>
                        @foreach ($availableCharacters as $character)
                            <option value="{{ $character->id }}">{{ $character->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Actor de voz (opcional)</span></label>
                    <select name="person_id" class="select select-bordered select-sm w-full">
                        <option value="">Sin asignar</option>
                        @foreach ($availablePeople->where('is_voice_actor', true) as $person)
                            <option value="{{ $person->id }}">{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Agregar</button>
            </form>
        </div>
    </div>

    {{-- Personajes asignados --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base mb-3">Personajes asignados</h2>

            @if ($anime->characters->isEmpty())
                <p class="text-base-content/60 text-sm">Sin personajes asignados.</p>
            @else
                <div class="space-y-4">
                    @foreach ($anime->characters as $character)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 bg-base-200 rounded-box">
                            <div class="flex-1">
                                <p class="font-medium">{{ $character->name }}</p>
                                @if ($character->voiceActors->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach ($character->voiceActors as $va)
                                            <div class="flex items-center gap-1 text-sm text-base-content/70">
                                                <span>🎙 {{ $va->name }}</span>
                                                <form method="POST" action="{{ route('admin.animes.voiceactor.detach', [$anime, $character, $va]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-ghost text-error">✕</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-base-content/50 mt-1">Sin actor de voz asignado.</p>
                                @endif
                            </div>

                            <div class="flex gap-2 items-center">
                                {{-- Agregar seiyuu --}}
                                <form method="POST" action="{{ route('admin.animes.voiceactor.attach', [$anime, $character]) }}" class="flex gap-2">
                                    @csrf
                                    <select name="person_id" class="select select-bordered select-xs">
                                        <option value="">+ Seiyuu</option>
                                        @foreach ($availablePeople->where('is_voice_actor', true) as $person)
                                            <option value="{{ $person->id }}">{{ $person->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-xs btn-outline">Asignar</button>
                                </form>

                                {{-- Quitar personaje --}}
                                <form method="POST" action="{{ route('admin.animes.characters.detach', [$anime, $character]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error btn-outline"
                                            onclick="return confirm('¿Quitar este personaje del anime?')">Quitar</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>