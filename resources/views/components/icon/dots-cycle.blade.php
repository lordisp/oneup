@props(['solid' => false,'size' => 5])
@if($solid)
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
    </svg>
@else
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endif