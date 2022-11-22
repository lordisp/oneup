@props(['active' => true])
@php
    $classes = ($active ?? false)
                ? 'btn-primary'
                : 'btn-primary-disabled';
@endphp
<button @if(!$active) disabled @endif{{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
    {{ $slot }}
</button>
