<button {{ $attributes->merge(['type' => 'submit','class'=> 'hover:underline']) }}>
    {{ $slot }}
</button>
