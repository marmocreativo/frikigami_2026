<x-layouts.admin :title="'Editar rol · ' . $user->name">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">← Volver</a>
        <h1 class="text-2xl font-bold">Editar rol de usuario</h1>
    </div>

    <div class="card bg-base-100 shadow-sm max-w-md">
        <div class="card-body">
            <div class="mb-4">
                <p class="text-sm text-base-content/60">Usuario</p>
                <p class="font-bold text-lg">{{ $user->name }}</p>
                <p class="text-sm text-base-content/60">{{ $user->email }}</p>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="form-control">
                    <label class="label"><span class="label-text">Rol</span></label>
                    <select name="role" class="select select-bordered w-full @error('role') select-error @enderror" required>
                        <option value="user" @selected($user->role === 'user')>Usuario</option>
                        <option value="admin" @selected($user->role === 'admin')>Administrador</option>
                    </select>
                    @error('role')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">Actualizar rol</button>
            </form>
        </div>
    </div>
</x-layouts.admin>