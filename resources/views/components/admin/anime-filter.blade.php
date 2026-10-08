@props(['animes', 'current' => null])

<select name="anime" class="select select-sm w-full sm:w-56" onchange="this.form.submit()">
    <option value="">Todos los animes</option>
    @foreach ($animes as $anime)
        <option value="{{ $anime->slug }}" @selected($current === $anime->slug)>{{ $anime->title }}</option>
    @endforeach
</select>