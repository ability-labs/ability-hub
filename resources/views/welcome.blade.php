

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Ability Hub</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            /* ! tailwindcss v3.4.17 | MIT License | https://tailwindcss.com */
            /* (Tailwind CSS styles are included here as in the original template) */
        </style>
    @endif
</head>
<body class="font-sans antialiased dark:bg-black dark:text-white/50">
<div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">

    <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#f6339a] selection:text-white">
        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
            <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                <div class="flex lg:justify-center lg:col-start-2">
                    <x-application-logo class="h-40" />
                </div>
                @if (Route::has('login'))
                    <nav class="-mx-3 flex items-center flex-1 justify-end">

                        @auth
                            <a href="{{ url('/dashboard') }}"
                               class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                {{__('Dashboard')}}
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                {{ __('Login') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                    {{__('Register')}}
                                </a>
                            @endif
                        @endauth
                            <x-language-select />
                    </nav>
                @endif
            </header>

            <main class="mt-6">

                <h1 class="text-3xl font-bold text-black dark:text-white mb-16">
                    {{ __('Welcome to :app', ['app' => config('app.name')]) }}
                </h1>
                <div class="grid grid-cols-2 gap-8">
                    @foreach(\App\Models\ContentCard::query()->wherePage('welcome')->get() as $content)
                        <div class="max-w-md mx-auto">
                            <x-content-card :content="$content" />
                        </div>
                    @endforeach
                </div>
            </main>

            <footer class="py-16 text-center text-sm  text-gray-400 dark:text-white/70">
               <span class="font-bold text-gray-500">&copy; {{ date('Y') }} - Ability Hub</span> <x-app-version />
            </footer>
        </div>
    </div>
</div>
</body>
</html>
