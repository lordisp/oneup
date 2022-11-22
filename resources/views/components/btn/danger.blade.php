@props(['active' => true])
<button @if(!$active) disabled @endif {{ $attributes->merge(['type'=>'button', 'class'=>'btn-waring-secondary']) }}>
    {{ $slot }}
</button>
