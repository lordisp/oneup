<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 ">
<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#05164d">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$title ?? config('app.name')}}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
{{ $slot }}
</body>
@livewireScripts
</html>