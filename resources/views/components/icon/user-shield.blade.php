@props(['solid' => false,'size' => 5])
@aware(['active'])
@php
    $classes = ($active ?? false) ? 'text-gray-500' : 'text-gray-400';
@endphp

@if($solid)
    <svg {{ $attributes->merge(['class' => $classes.' h-'.$size.' w-'.$size]) }} aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-shield" class="svg-inline--fa fa-user-shield" role="img"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
        <path fill="currentColor"
              d="M622.3 271.1l-115.1-45.01c-4.125-1.629-12.62-3.754-22.25 0L369.8 271.1C359 275.2 352 285.1 352 295.1c0 111.6 68.75 188.8 132.9 213.9c9.625 3.75 18 1.625 22.25 0C558.4 489.9 640 420.5 640 295.1C640 285.1 633 275.2 622.3 271.1zM496 462.4V273.2l95.5 37.38C585.9 397.8 530.6 446 496 462.4zM224 256c70.7 0 128-57.31 128-128S294.7 0 224 0C153.3 0 96 57.31 96 128S153.3 256 224 256zM320.6 310.3C305.9 306.3 290.6 304 274.7 304H173.3C77.61 304 0 381.7 0 477.4C0 496.5 15.52 512 34.66 512H413.3c3.143 0 5.967-1.004 8.861-1.789C369.7 469.8 324.1 400.3 320.6 310.3z"></path>
    </svg>
@else
    <svg {{ $attributes->merge(['class' => $classes.' h-'.$size.' w-'.$size]) }} aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-shield" class="svg-inline--fa fa-user-shield" role="img"
         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
        <path fill="currentColor"
              d="M622.3 271.1l-115.1-45.01c-4.125-1.629-12.62-3.754-22.25 0L369.8 271.1C359 275.2 352 285.1 352 295.1c0 111.6 68.75 188.8 132.9 213.9c9.625 3.75 18 1.625 22.25 0C558.4 489.9 640 420.5 640 295.1C640 285.1 633 275.2 622.3 271.1zM496 462.4V273.2l95.5 37.38C585.9 397.8 530.6 446 496 462.4zM224 256c70.7 0 128-57.31 128-128S294.7 0 224 0C153.3 0 96 57.31 96 128S153.3 256 224 256zM320.6 310.3C305.9 306.3 290.6 304 274.7 304H173.3C77.61 304 0 381.7 0 477.4C0 496.5 15.52 512 34.66 512H413.3c3.143 0 5.967-1.004 8.861-1.789C369.7 469.8 324.1 400.3 320.6 310.3z"></path>
    </svg>
@endif