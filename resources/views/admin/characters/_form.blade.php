@csrf

<div class="form-control">
    <label class="label" for="name">
        <span class="label-text">Nombre</span>
    </label>
    <input id="name" type="text" name="name" value="{{ old('name', $character->name ?? '') }}"
           class="input input-bordered w-full @error('name') input-error @enderror"
           required autofocus>
    @error('name')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
    @error('slug')
        <span class="text-error text-sm mt-1">Ya existe un personaje con un nombre equivalente.</span>
    @enderror
</div>

<div class="form-control">
    <label class="label" for="photo">
        <span class="label-text">Foto</span>
    </label>
    @if (isset($character) && $character->photo)
        <img src="{{ Storage::url($character->photo) }}" alt="Foto actual" class="w-24 rounded mb-2">
    @endif
    <input id="photo" type="file" name="photo" accept="image/*"
           class="file-input file-input-bordered w-full @error('photo') file-input-error @enderror">
    @error('photo')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="form-control">
    <label class="label" for="description">
        <span class="label-text">Descripción</span>
    </label>
    <textarea id="description" name="description" rows="5"
              class="textarea textarea-bordered w-full @error('description') textarea-error @enderror">{{ old('description', $character->description ?? '') }}</textarea>
    @error('description')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($character) ? 'Actualizar Personaje' : 'Crear Personaje' }}
</button>
