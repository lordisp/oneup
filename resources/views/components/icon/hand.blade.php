@props(['solid' => false,'size' => 5])
@aware(['active'])
@php
    $classes = ($active ?? false) ? 'text-gray-500' : 'text-gray-400';
@endphp

@if($solid)
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $classes.' h-'.$size.' w-'.$size]) }} viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M9 3a1 1 0 012 0v5.5a.5.5 0 001 0V4a1 1 0 112 0v4.5a.5.5 0 001 0V6a1 1 0 112 0v5a7 7 0 11-14 0V9a1 1 0 012 0v2.5a.5.5 0 001 0V4a1 1 0 012 0v4.5a.5.5 0 001 0V3z" clip-rule="evenodd" />
    </svg>
@else
    <svg xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $classes.' h-'.$size.' w-'.$size]) }} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
    </svg>
@endif