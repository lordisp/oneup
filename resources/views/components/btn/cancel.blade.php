<button {{ $attributes->merge(['type' => 'button', 'class' => ' border border-lh-blue disabled:opacity-25 duration-150 ease-in-out focus:outline-none font-medium hover:bg-gray-50 px-3 py-1.5 rounded-md text-base text-center text-lh-blue transition']) }}>
    {{ $slot }}
</button>