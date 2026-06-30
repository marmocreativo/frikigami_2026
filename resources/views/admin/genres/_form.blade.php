<div class="form-control">
    <label class="label"><span class="label-text">Nombre</span></label>
    <input type="text" name="name" value="{{ old('name', $genre->name ?? '') }}"
           class="input input-bordered w-full @error('name') input-error @enderror"
           required autofocus placeholder="Ej. Acción, Comedia, Romance...">
    @error('name')<span class="text-error text-sm mt-1">{{ $message }}</span>@enderror
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($genre) ? 'Actualizar Género' : 'Crear Género' }}
</button>