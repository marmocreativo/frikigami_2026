@props(['value' => 3, 'size' => 'text-lg'])

{{-- Estrellas de solo lectura (permite medias estrellas visualmente: 3.5 llena 70%) --}}
@php $percent = max(0, min(100, ((float) $value) / 5 * 100)); @endphp
<span {{ $attributes->merge(['class' => "relative inline-block leading-none tracking-tight select-none $size"]) }}
      role="img" aria-label="{{ number_format((float) $value, 1) }} de 5 estrellas">
    <span class="text-base-content/20" aria-hidden="true">★★★★★</span>
    <span class="absolute inset-y-0 left-0 overflow-hidden whitespace-nowrap text-warning" style="width: {{ $percent }}%" aria-hidden="true">★★★★★</span>
</span>
