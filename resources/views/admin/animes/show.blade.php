<x-layouts.admin :title="$anime->title">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.animes.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">{{ $anime->title }}</h1>
        <a href="{{ route('animes.show', $anime) }}" class="btn btn-ghost btn-xs" target="_blank">Ver público →</a>
    </div>

    @include('admin.partials.alert')

    <div role="tablist" class="tabs tabs-lift mb-6">
        <a href="{{ route('admin.animes.show', ['anime' => $anime, 'tab' => 'cast']) }}"
           role="tab" class="tab {{ $tab === 'cast' ? 'tab-active' : '' }}">Cast (Staff)</a>
        <a href="{{ route('admin.animes.show', ['anime' => $anime, 'tab' => 'characters']) }}"
           role="tab" class="tab {{ $tab === 'characters' ? 'tab-active' : '' }}">Personajes</a>
    </div>

    @if ($tab === 'cast')
        @include('admin.animes.tabs.cast')
    @else
        @include('admin.animes.tabs.characters')
    @endif
</x-layouts.admin>