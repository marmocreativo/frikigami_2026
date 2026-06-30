<x-layouts.admin title="Noticias">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Noticias</h1>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary btn-sm">+ Nueva Noticia</a>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Publicada</th>
                        <th>Animes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($news as $item)
                        <tr>
                            <td class="font-medium">{{ $item->title }}</td>
                            <td>{{ $item->user->name ?? '—' }}</td>
                            <td>
                                @if ($item->published_at)
                                    <span class="badge badge-sm badge-success">{{ $item->published_at->format('d/m/Y') }}</span>
                                @else
                                    <span class="badge badge-sm badge-warning">Borrador</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($item->animes as $anime)
                                        <span class="badge badge-xs badge-outline">{{ $anime->title }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    @if ($item->published_at)
                                        <a href="{{ route('news.show', $item) }}" class="btn btn-ghost btn-xs" target="_blank">Ver</a>
                                    @endif
                                    <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-outline btn-xs">Editar</a>
                                    <form method="POST" action="{{ route('admin.news.destroy', $item) }}" onsubmit="return confirm('¿Eliminar esta noticia?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60 py-8">No hay noticias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $news->links() }}</div>
</x-layouts.admin>