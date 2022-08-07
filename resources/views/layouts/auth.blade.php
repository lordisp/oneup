<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 ">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'OneUp' }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="h-full">
{{ $slot }}
</body>
</html>