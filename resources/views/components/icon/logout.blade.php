@props(['solid' => false,'size' => 5])
@if($solid)

    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
    </svg>
@else
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
    </svg>
@endif