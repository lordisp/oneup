@props(['solid' => false,'size' => 5])
@aware(['active'])
@php
    $classes = ($active ?? false) ? 'text-gray-500' : 'text-gray-400';
@endphp
@if($solid)
    <svg {{ $attributes->merge(['class' => $classes.' h-'.$size.' w-'.$size]) }} xmlns="http://www.w3.org/2000/svg"      viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
              d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
              clip-rule="evenodd"/>
    </svg>
@else
    <svg {{ $attributes->merge(['class' => $classes.' h-'.$size.' w-'.$size]) }} xmlns="http://www.w3.org/2000/svg"      fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
    </svg>
@endif