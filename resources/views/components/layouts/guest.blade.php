<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Frikigami' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <a href="{{ route('login') }}" class="text-3xl font-bold">Frikigami</a>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>