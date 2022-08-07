@props([
    'modal' => 'default'
])
@aware(['panel'])
<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail.modal == '{{ $modal }}') open = true"
     x-on:close-modal.window="if ($event.detail.modal == '{{ $modal }}') open = false"
     class="flex justify-center"
>

    <!-- Modal -->
    <div
            x-show="open"
            style="display: none"
            x-on:keydown.escape.prevent.stop="open = false"
            role="dialog"
            aria-modal="true"
            x-id="['modal-title']"
            :aria-labelledby="$id('modal-title')"
            class="fixed inset-0 overflow-y-auto z-10"
    >
        <!-- Overlay -->
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>

        <!-- Panel -->
        {{ $slot }}
    </div>
</div>