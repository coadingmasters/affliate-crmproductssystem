@php
    $navigation = [
        [
            'route' => 'order.create',
            'label' => 'Place Order',
            'active' => 'order.create',
            'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3A1 1 0 005.4 17H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        ],
        [
            'route' => 'order.list',
            'label' => 'All Orders',
            'active' => 'order.list|order.show',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        ],
        [
            'route' => 'order.history',
            'label' => 'My Dashboard',
            'active' => 'order.history',
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-surface font-sans text-ink antialiased">

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside id="sidebar"
               class="fixed inset-y-0 left-0 z-40 hidden w-64 shrink-0 flex-col bg-sidebar lg:flex">
            <div class="flex h-16 items-center gap-2.5 border-b border-white/10 px-6">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand to-brand2 text-white shadow-lg shadow-brand/25">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </span>
                <div class="leading-tight">
                    <p class="text-sm font-semibold text-white">Med Alert</p>
                    <p class="text-[11px] text-slate-400">Customer Portal</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4">
                @foreach ($navigation as $item)
                    @php $isActive = request()->routeIs($item['active']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium
                              {{ $isActive
                                  ? 'is-active bg-white/10 text-white'
                                  : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-[18px] w-[18px] {{ $isActive ? 'text-brand2' : '' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-3">
                <div class="mb-2 flex items-center gap-2.5 rounded-xl px-3 py-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand to-brand2 text-xs font-bold text-white">
                        {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 leading-tight">
                        <p class="truncate text-xs font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-danger/15 hover:text-danger">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        {{-- Backdrop for the mobile sidebar --}}
        <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-black/60 backdrop-blur-sm lg:hidden"></div>

        {{-- Content --}}
        <div class="flex min-h-screen w-full flex-col lg:pl-64">

            <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-line bg-card/80 px-4 backdrop-blur-xl sm:px-6">
                <button type="button" id="sidebar-toggle"
                        class="rounded-lg p-2 text-muted transition hover:bg-elevated hover:text-ink lg:hidden"
                        aria-label="Toggle navigation">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-base font-semibold text-ink sm:text-lg">@yield('heading', 'Dashboard')</h1>
                </div>

                @unless (request()->routeIs('order.create'))
                    <a href="{{ route('order.create') }}"
                       class="hidden items-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-brand/25 transition hover:opacity-90 sm:inline-flex">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Order
                    </a>
                @endunless
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="pop mb-5 flex items-start gap-3 rounded-2xl border border-success/30 bg-success/10 px-4 py-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-success" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-success">{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="pop mb-5 flex items-start gap-3 rounded-2xl border border-danger/30 bg-danger/10 px-4 py-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-danger" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.71-3L13.71 4a2 2 0 00-3.42 0L3.36 16a2 2 0 001.71 3z"/>
                        </svg>
                        <span class="text-sm font-medium text-danger">Please check the highlighted fields below.</span>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="border-t border-line bg-card/60 px-4 py-5 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-3 text-xs text-muted sm:flex-row">
                    <p>&copy; {{ date('Y') }} Med Alert. All rights reserved.</p>

                    <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Secure checkout
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            24/7 support
                        </span>
                        <span>Signed in as {{ auth()->user()->email }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const toggle = document.getElementById('sidebar-toggle');

            function close() {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('flex');
                backdrop.classList.add('hidden');
            }

            toggle.addEventListener('click', function () {
                const isHidden = sidebar.classList.contains('hidden');
                sidebar.classList.toggle('hidden', !isHidden);
                sidebar.classList.toggle('flex', isHidden);
                backdrop.classList.toggle('hidden', !isHidden);
            });

            backdrop.addEventListener('click', close);
        })();
    </script>

    @include('partials.modal')

    @stack('scripts')
</body>
</html>
