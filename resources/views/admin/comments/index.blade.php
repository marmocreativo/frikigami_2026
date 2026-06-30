<x-layouts.admin title="Comentarios">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Comentarios</h1>
    </div>

    @include('admin.partials.alert')

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Comentario</th>
                        <th>En</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                        <tr>
                            <td class="font-medium whitespace-nowrap">
                                {{ $comment->user->name ?? 'Desconocido' }}
                            </td>
                            <td class="max-w-xs">
                                <p class="truncate">{{ $comment->body }}</p>
                            </td>
                            <td class="whitespace-nowrap">
                                @if ($comment->commentable)
                                    @php
                                        $type = class_basename($comment->commentable_type);
                                        $label = $type === 'News'
                                            ? $comment->commentable->title
                                            : 'Ep. ' . $comment->commentable->number . ' · ' . $comment->commentable->animeSeason?->anime?->title;
                                    @endphp
                                    <span class="badge badge-sm badge-outline">{{ $type }}</span>
                                    <span class="text-xs text-base-content/60 block truncate max-w-32">{{ $label }}</span>
                                @else
                                    <span class="text-base-content/40 text-xs">Eliminado</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-base-content/60 text-xs">
                                {{ $comment->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}"
                                      onsubmit="return confirm('¿Eliminar este comentario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60 py-8">No hay comentarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $comments->links() }}</div>
</x-layouts.admin>