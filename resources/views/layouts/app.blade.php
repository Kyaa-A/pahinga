<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-init="
    if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Pahinga</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('pahinga.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('pahinga.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900" x-data="{ loading: false }" @livewire-navigate-start.window="loading = true" @livewire-navigate-end.window="loading = false">
        <!-- Loading indicator for page navigation -->
        <div x-show="loading" x-cloak class="fixed top-0 left-0 right-0 z-50">
            <div class="h-1 bg-gradient-horizon animate-pulse"></div>
        </div>
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Flash Messages -->
            <x-flash-message />

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
