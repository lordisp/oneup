<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>OneUp</title>
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        @vite(['resources/css/app.css','resources/js/app.js'])
    </head>
    <body>
        <div class="font-text text-gray-900 antialiased">
            {{ $slot }}
        </div>

        <x-modal init="{{session('error')}}" title="Error">
            {{ session('error')}}
            <x-slot name="button">
                <button @click="open=false" type="button" class="btn-waring-secondary">
                    Close
                </button>
            </x-slot>
        </x-modal>
    </body>
</html>
