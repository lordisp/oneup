@props([
    'type'=>'false'
])

@php
    $file=match($type){
    'sm'=>'oneup_thumb.png',
    'dark'=>'oneup_logo_dark.png',
    default=>'oneup_logo.png',
    };
@endphp

<img {{$attributes}} src="/images/logos/{{$file}}" alt="OneUp Logo">
