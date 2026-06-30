<div class="grid grid-cols-2 gap-4">
    <div class="form-control">
        <label class="label"><span class="label-text">Nombre</span></label>
        <select name="name" class="select select-bordered w-full @error('name') select-error @enderror" required>
            <option value="">Selecciona una temporada</option>
            @php $selected = old('name', $season->name ?? null); @endphp
            @foreach (['Invierno', 'Primavera', 'Verano', 'Otoño'] as $name)
                <option value="{{ $name }}" @selected($selected === $name)>{{ $name }}</option>
            @endforeach
        </select>
        @error('name')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </div>

    <div class="form-control">
        <label class="label"><span class="label-text">Año</span></label>
        <input type="number" name="year" value="{{ old('year', $season->year ?? date('Y')) }}"
               class="input input-bordered w-full @error('year') input-error @enderror"
               min="1900" max="2100" required>
        @error('year')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
    </div>
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($season) ? 'Actualizar Temporada' : 'Crear Temporada' }}
</button>