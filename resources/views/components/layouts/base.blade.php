<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased bg-gray-100 js-focus-visible">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __($title) ?? null }} {{ __($title) ?? false ? '|' : null }} {{ config('app.name') }}</title>

    @livewireStyles
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Commissioner:wght@200;300;400;500;600;700&amp;family=JetBrains+Mono:ital@0;1&amp;display=swap">
    <link rel="stylesheet" href="{{ route('filament.asset', [
        'id' => Filament\get_asset_id('/css/filament.css'),
        'path' => 'css/filament.css',
    ]) }}" />

    @foreach (\Filament\Filament::getStyles() as $path)
        <link rel="stylesheet" href="{{ $path }}" />
    @endforeach

    @stack('filament-styles')
</head>

<body class="text-gray-600">
    {{ $slot }}

    <x-filament::notification />

    @livewireScripts
    <script>
        window.filamentConfig = @json(\Filament\Filament::getScriptData());
    </script>

    <script src="{{ route('filament.asset', [
        'id' => Filament\get_asset_id('/js/filament.js'),
        'path' => 'js/filament.js',
    ]) }}"></script>

    @foreach (\Filament\Filament::getScripts() as $path)
        <script src="{{ $path }}"></script>
    @endforeach

    @stack('filament-scripts')
</body>
</html>
