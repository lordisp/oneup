@props(['solid' => false,'size' => 5])
@if($solid)
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
    </svg>
@else
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'h-'.$size.' w-'.$size]) }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
    </svg>
@endif