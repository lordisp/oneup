<!DOCTYPE html>
<html
    x-data="{'darkMode': false}"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => {
            localStorage.setItem('darkMode', JSON.stringify(value))
            Array.from(document.querySelectorAll('.tooltips')).forEach(
              el => el._tippy.setProps({ theme: value ? 'light-border':null }))
        })"
    x-cloak
    :class="{'dark ': darkMode === true}"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#05164d">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$title ?? config('app.name')}}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased h-full">
<div x-data="{ open: false }" @keydown.window.escape="open = false" class="font-text text-lhg-blue dark:text-lhg-gray-12 bg-white dark:bg-gray-800 min-h-screen">
    <x-sidebar/>
    <div class="md:pl-28 xl:pl-64 flex flex-col flex-1 ">
        <!-- Navbar -->
        <x-navbar/>
        <main>
            {{$slot}}
        </main>
    </div>
</div>
<x-btn.to-top/>
<x-notification/>
@livewireScripts
</body>
</html>
