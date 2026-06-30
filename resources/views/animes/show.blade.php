<x-layouts.app :title="$anime->title">
    <div class="max-w-5xl mx-auto">
        @if (session('status'))
            <div class="alert alert-success mb-4">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="flex flex-col md:flex-row gap-6 mb-6">
            @if ($anime->cover_image)
                <img src="{{ Storage::url($anime->cover_image) }}" alt="{{ $anime->title }}" class="w-full md:w-64 rounded-box shadow">
            @endif

            <div class="flex-1">
                <h1 class="text-3xl font-bold">{{ $anime->title }}</h1>

                <div class="flex flex-wrap gap-2 my-2">
                    <span class="badge badge-primary">{{ ucfirst($anime->status) }}</span>
                    @if ($anime->season)
                        <span class="badge badge-outline">{{ $anime->season->label }}</span>
                    @endif
                    @foreach ($anime->genres as $genre)
                        <span class="badge badge-outline">{{ $genre->name }}</span>
                    @endforeach
                </div>

                @auth
                    <div class="flex gap-2 mt-2">
                        <a href="{{ route('animes.edit', $anime) }}" class="btn btn-sm btn-outline">Editar</a>
                        <form method="POST" action="{{ route('animes.destroy', $anime) }}" onsubmit="return confirm('¿Eliminar este anime?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-error btn-outline">Eliminar</button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>

        <div role="tablist" class="tabs tabs-lift mb-6">
            @php
                $tabs = [
                    'info' => 'Información',
                    'cast' => 'Cast',
                    'characters' => 'Personajes',
                    'episodes' => 'Episodios',
                    'news' => 'Noticias',
                ];
            @endphp

            @foreach ($tabs as $key => $label)
                <a href="{{ route('animes.show', ['anime' => $anime, 'tab' => $key]) }}"
                   role="tab"
                   class="tab {{ $tab === $key ? 'tab-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div>
            @switch($tab)
                @case('cast')
                    @include('animes.tabs.cast')
                    @break

                @case('characters')
                    @include('animes.tabs.characters')
                    @break

                @case('episodes')
                    @include('animes.tabs.episodes')
                    @break

                @case('news')
                    @include('animes.tabs.news')
                    @break

                @default
                    @include('animes.tabs.info')
            @endswitch
        </div>
    </div>
</x-layouts.app>