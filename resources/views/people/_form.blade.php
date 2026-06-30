@csrf

<div class="form-control">
    <label class="label" for="name">
        <span class="label-text">Nombre</span>
    </label>
    <input id="name" type="text" name="name" value="{{ old('name', $person->name ?? '') }}"
           class="input input-bordered w-full @error('name') input-error @enderror"
           required autofocus>
    @error('name')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="form-control">
    <label class="label" for="photo">
        <span class="label-text">Foto</span>
    </label>
    @if (isset($person) && $person->photo)
        <img src="{{ Storage::url($person->photo) }}" alt="Foto actual" class="w-24 rounded mb-2">
    @endif
    <input id="photo" type="file" name="photo"
           class="file-input file-input-bordered w-full @error('photo') file-input-error @enderror">
    @error('photo')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="form-control">
    <label class="label" for="bio">
        <span class="label-text">Biografía</span>
    </label>
    <textarea id="bio" name="bio" rows="4"
              class="textarea textarea-bordered w-full @error('bio') textarea-error @enderror">{{ old('bio', $person->bio ?? '') }}</textarea>
    @error('bio')
        <span class="text-error text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="flex gap-6">
    <label class="label cursor-pointer gap-2">
        <input type="checkbox" name="is_voice_actor" value="1" class="checkbox"
               @checked(old('is_voice_actor', $person->is_voice_actor ?? false))>
        <span class="label-text">Es actor de voz (seiyuu)</span>
    </label>

    <label class="label cursor-pointer gap-2">
        <input type="checkbox" name="is_staff" value="1" class="checkbox"
               @checked(old('is_staff', $person->is_staff ?? false))>
        <span class="label-text">Es staff de producción</span>
    </label>
</div>

<button type="submit" class="btn btn-primary w-full">
    {{ isset($person) ? 'Actualizar Persona' : 'Crear Persona' }}
</button>