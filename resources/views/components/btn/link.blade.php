@props(['disabled'=>false])
<button {{ $disabled ? 'disabled' : '' }}  {{ $attributes->merge(['type' => 'submit','class'=> 'hover:underline']) }}>
    {{ $slot }}
</button>
