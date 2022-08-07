@props(['solid' => false,'size' => 5])
@if($solid)
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
    </svg>
@else
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
    </svg>
@endif
