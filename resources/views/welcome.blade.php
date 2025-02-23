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

    <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
            <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                <div class="flex lg:justify-center lg:col-start-2">
                    <x-application-logo class="h-40" />
                </div>
                @if (Route::has('login'))
                    <nav class="-mx-3 flex flex-1 justify-end">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                               class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main class="mt-6">
                <div class="space-y-6">
                    <h1 class="text-3xl font-bold text-black dark:text-white">Welcome to Ability Hub</h1>

                    <p class="text-sm/relaxed text-black dark:text-white">
                        Ability Hub is a comprehensive suite of tools designed for the analysis and monitoring of skill development in the ABA (Applied Behavior Analysis) domain, tailored specifically for individuals with special needs.
                    </p>

                    <p class="text-sm/relaxed text-black dark:text-white">
                        Our platform empowers educators, therapists, and intervention specialists to collect and analyze data, develop personalized training programs, and design dynamic learning paths that adapt to each user’s unique requirements.
                    </p>

                    <p class="text-sm/relaxed text-black dark:text-white">
                        With real-time progress tracking, both students and their legal guardians can stay informed about academic and developmental milestones. This transparency fosters a collaborative environment where continuous improvement is prioritized.
                    </p>

                    <p class="text-sm/relaxed text-black dark:text-white">
                        In addition, Ability Hub offers interactive services such as parent training modules, live streaming sessions, and a variety of engagement tools that enhance the learning experience and support ongoing intervention strategies.
                    </p>
                </div>
            </main>

            <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                Ability Hub &copy; {{ date('Y') }} – Built with Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </footer>
        </div>
    </div>
</div>
</body>
</html>
