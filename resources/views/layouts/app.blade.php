<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen font-sans text-ink antialiased">

    <div class="orb orb-a" aria-hidden="true"></div>
    <div class="orb orb-b" aria-hidden="true"></div>

    <main class="relative z-10 mx-auto flex min-h-screen w-full max-w-3xl flex-col justify-center px-3 py-5 sm:px-6 sm:py-8">
        @yield('content')
    </main>

    @include('partials.modal')

@stack('scripts')
</body>
</html>
