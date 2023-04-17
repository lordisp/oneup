<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-cancel']) }}>
    {{ $slot }}
</button>