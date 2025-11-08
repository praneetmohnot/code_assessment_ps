<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Coral') }}</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('geo-zones.index') }}">{{ config('app.name', 'Coral') }}</a>
            <div class="d-flex gap-2">
                <a class="nav-link" href="{{ route('geo-zones.index') }}">Geo Zones</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        {{ $slot }}
    </main>

    <script src="{{ mix('js/app.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
