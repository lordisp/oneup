<button {{ $attributes->merge(['type'=>'button', 'class'=>'btn-primary']) }}>
    {{ $slot }}
</button>
