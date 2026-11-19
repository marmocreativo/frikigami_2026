<x-layouts.admin title="Sliders del inicio">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Sliders del inicio</h1>
        <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary btn-sm">+ Nuevo Slider</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th class="w-32"></th>
                        <th>Título</th>
                        <th>Anime</th>
                        <th>Link</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slides as $slide)
                        <tr>
                            <td>
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" loading="lazy"
                                     class="w-28 h-16 object-cover rounded">
                            </td>
                            <td class="font-medium">{{ $slide->title }}</td>
                            <td>{{ $slide->anime?->title ?? '—' }}</td>
                            <td class="max-w-48 truncate" title="{{ $slide->target_url }}">
                                @if ($slide->target_url)
                                    <a href="{{ $slide->target_url }}" target="_blank" class="link link-hover text-xs">{{ $slide->link ?: 'Ficha del anime' }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $slide->position }}</td>
                            <td>
                                <span class="badge badge-sm {{ $slide->is_active ? 'badge-success' : 'badge-ghost' }}">
                                    {{ $slide->is_active ? 'Activo' : 'Oculto' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('admin.sliders.edit', $slide) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.sliders.destroy', $slide) }}" onsubmit="return confirm('¿Eliminar este slider?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-base-content/60 py-8">
                                Todavía no hay sliders. Mientras no haya ninguno activo, el inicio muestra la última noticia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
