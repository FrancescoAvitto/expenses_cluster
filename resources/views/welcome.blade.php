<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Gestione Spese') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md px-8 py-10 bg-white shadow-lg sm:rounded-xl text-center">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestione Spese</h1>
            <p class="text-gray-500">Tieni traccia delle tue entrate e uscite in modo semplice e veloce.</p>
        </div>

        <div class="flex flex-col gap-4">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-[#374151] border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-[#374151] focus:ring-offset-2 transition ease-in-out duration-150">
                        Vai alla Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-[#374151] border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-[#374151] focus:ring-offset-2 transition ease-in-out duration-150">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="w-full inline-flex justify-center items-center px-4 py-3 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#374151] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Registrati
                        </a>
                    @endif
                @endauth
            @endif
        </div>

    </div>

</body>

</html>