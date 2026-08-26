<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Frikigami' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'frikigami-light');
    </script>
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
    <x-navbar />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>