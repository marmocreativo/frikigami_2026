<x-layouts.admin title="Usuarios">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Usuarios</h1>
    </div>

    @include('admin.partials.alert')

    @if (session('error'))
        <div class="alert alert-error mb-4">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Noticias</th>
                        <th>Episodios</th>
                        <th>Comentarios</th>
                        <th>Registro</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="font-medium">{{ $user->name }}</td>
                            <td class="text-base-content/60">{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-sm {{ $user->role === 'admin' ? 'badge-primary' : 'badge-ghost' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>{{ $user->news_count }}</td>
                            <td>{{ $user->episodes_count }}</td>
                            <td>{{ $user->comments_count }}</td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-xs">Rol</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('¿Eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-error btn-xs btn-outline">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-base-content/60 py-8">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>