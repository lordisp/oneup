<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>We'll be right there for you | OneUp</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-lhg-gray-6">

<div class="relative py-16 ">
    <div class="absolute bg-gray-50 h-1/2 hidden inset-x-0 lg:block shadow-xl top-0" aria-hidden="true"></div>
    <div class="absolute top-0 inset-x-0 h-1/4 bg-gray-50 lg:hidden" aria-hidden="true"></div>
    <div class="absolute border-b-1 pb-2 px-2 shadow-sm top-0 w-screen">
        <div class="inset-x-2 items-center flex justify-between max-w-7xl mt-2 mx-auto">
            <img src="{{asset('images/logos/LHG_Crane_blue.svg')}}" class="hidden w-12 h-12 sm:block" alt="Crane">
            <img src="{{asset('images/logos/LHG_Wordmark_blue.svg')}}" class="h-3.5 sm:h-5" alt="Wordmark">
        </div>
    </div>
    .
    <div class="absolute top-0 w-full border-b-2 shadow-lg>"></div>
    <div class="bg-lhg-blue lg:bg-transparent lg:px-8 max-w-7xl mx-auto py-8 sm:py-24">
        <div class="lg:grid lg:grid-cols-12">
            <div class="relative z-10 lg:col-start-1 lg:row-start-1 lg:col-span-4 lg:py-16 lg:bg-transparent">
                <div class="absolute inset-x-0 h-1/2 bg-gray-50 lg:hidden -mt-5 md:mt-0 rounded-xl md:rounded-3xl"
                     aria-hidden="true"></div>
                <div class="max-w-md mx-auto px-4 sm:max-w-3xl sm:px-6 lg:max-w-none lg:p-0 relative">
                    <div class="lg:hidden mt-10 text-white absolute items-center flex justify-center inset-x-0 w-full h-1/2 z-50 opacity-90">
                        <div class="p-4 mx-2 rounded-md border border-gray-200 border-opacity-75 bg-white shrink">
                            <h2>We'll be
                                <span class="before:block before:absolute before:-inset-1 before:-skew-y-3 before:bg-[#FFB000] relative inline-block">
                                <span class="relative text-lhg-blue">right there</span>
                            </span>
                                for you...
                            </h2>
                        </div>
                    </div>
                    <div class="aspect-w-10 aspect-h-6 sm:aspect-w-2 sm:aspect-h-1 lg:aspect-w-1">
                        <img class="object-cover object-center rounded-3xl shadow-2xl"
                             src="{{asset('images/a31_BEC7137.jpg')}}" alt="Cover">
                    </div>
                </div>
            </div>
            <div class="relative bg-lhg-blue lg:col-start-3 lg:row-start-1 lg:col-span-10 lg:rounded-3xl lg:grid lg:grid-cols-10 lg:items-center shadow-2xl">
                <div class="hidden absolute inset-0 overflow-hidden rounded-3xl lg:block" aria-hidden="true">

                    <svg class="absolute bottom-full left-full transform translate-y-1/3 -translate-x-2/3 xl:bottom-auto xl:top-0 xl:translate-y-0"
                         width="404" height="384" fill="none"
                         viewBox="0 0 404 384"
                         aria-hidden="true">
                        <defs>
                            <pattern id="64e643ad-2176-4f86-b3d7-f2c5da3b6a6d" x="0" y="0" width="20" height="20"
                                     patternUnits="userSpaceOnUse">
                                <rect x="0" y="0" width="4" height="4" class="text-blue-900" fill="currentColor"/>
                            </pattern>
                        </defs>
                        <rect width="404" height="384" fill="url(#64e643ad-2176-4f86-b3d7-f2c5da3b6a6d)"/>
                    </svg>
                    <svg class="absolute top-full transform -translate-y-1/3 -translate-x-1/3 xl:-translate-y-1/2"
                         width="404" height="384" fill="none" viewBox="0 0 404 384" aria-hidden="true">
                        <defs>
                            <pattern id="64e643ad-2176-4f86-b3d7-f2c5da3b6a6d" x="0" y="0" width="20" height="20"
                                     patternUnits="userSpaceOnUse">
                                <rect x="0" y="0" width="4" height="4" class="text-indigo-500" fill="currentColor"/>
                            </pattern>
                        </defs>
                        <rect width="404" height="384" fill="url(#64e643ad-2176-4f86-b3d7-f2c5da3b6a6d)"/>
                    </svg>
                </div>
                <div class="relative max-w-md mx-auto py-12 px-4 space-y-20 sm:max-w-3xl sm:py-16 sm:px-6 lg:max-w-none lg:p-0 lg:col-start-3 lg:col-span-6">
                    <div class="flex justify-center lg:mr-3">
                        <img src="{{ asset('images/logos/oneup_logo_dark.png') }}">
                    </div>
                    <div class="hidden lg:block mt-10 text-white">
                        <h2 class="text-white font-bold">We'll be
                            <span class="before:block before:absolute before:-inset-1 before:-skew-y-3 before:bg-lhg-yellow relative inline-block">
                                <span class="relative text-lhg-blue">right there</span>
                            </span>
                            for you...
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
