<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Staff actual --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base mb-3">Staff asignado</h2>

            @if ($anime->staff->isEmpty())
                <p class="text-base-content/60 text-sm">Sin staff asignado.</p>
            @else
                <table class="table table-sm">
                    <tbody>
                        @foreach ($anime->staff as $person)
                            <tr>
                                <td>{{ $person->name }}</td>
                                <td class="text-base-content/60">{{ $person->pivot->role }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.animes.staff.detach', [$anime, $person]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-error btn-outline">Quitar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Agregar staff --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base mb-3">Agregar staff</h2>

            <form method="POST" action="{{ route('admin.animes.staff.attach', $anime) }}" class="space-y-3">
                @csrf
                <div class="form-control">
                    <label class="label"><span class="label-text">Persona</span></label>
                    <select name="person_id" class="select select-bordered select-sm w-full" required>
                        <option value="">Selecciona una persona</option>
                        @foreach ($availablePeople as $person)
                            <option value="{{ $person->id }}">{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Rol</span></label>
                    <input type="text" name="role" class="input input-bordered input-sm w-full"
                           placeholder="Ej. Director, Guionista, Compositor..." required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-full">Agregar</button>
            </form>
        </div>
    </div>

</div>