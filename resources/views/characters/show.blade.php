<x-layouts.app :title="$character->name">
    <x-container class="pt-28">
        @if (session('status'))
            <div class="alert alert-success mb-4">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row gap-6">
            <div class="w-full md:w-64 shrink-0">
                @if ($character->photo)
                    <img src="{{ Storage::url($character->photo) }}" alt="{{ $character->name }}"
                         class="w-full aspect-[3/4] object-cover rounded-box shadow">
                @else
                    <div class="w-full aspect-[3/4] bg-base-300 rounded-box shadow flex items-center justify-center text-5xl text-base-content/30">
                        {{ strtoupper(substr($character->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="flex-1">
                <h1 class="text-3xl font-bold">{{ $character->name }}</h1>

                <p class="text-base text-base-content/70 mt-3">
                    {{ $character->description ?: 'Sin descripción disponible.' }}
                </p>

                @auth
                    @if (auth()->user()->isAdmin())
                        <div class="flex gap-2 mt-4">
                            <a href="{{ route('admin.characters.edit', $character) }}" class="btn btn-sm btn-outline">Editar</a>
                            <form method="POST" action="{{ route('admin.characters.destroy', $character) }}"
                                  onsubmit="return confirm('¿Eliminar este personaje?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-error btn-outline">Eliminar</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        @if ($character->animes->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-xl font-bold mb-4">Aparece en</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($character->animes as $anime)
                        @php
                            $actors = $character->voiceActors->where('pivot.anime_id', $anime->id);
                        @endphp

                        <div class="card bg-base-100 shadow-sm">
                            <div class="card-body p-4">
                                <a href="{{ route('animes.show', $anime) }}"
                                   class="font-bold hover:text-primary transition-colors">
                                    {{ $anime->title }}
                                </a>

                                @if ($actors->isNotEmpty())
                                    <div class="mt-1 space-y-1">
                                        @foreach ($actors as $person)
                                            <a href="{{ route('people.show', $person) }}"
                                               class="block text-sm text-base-content/60 hover:text-base-content">
                                                Voz: {{ $person->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-container>
</x-layouts.app>