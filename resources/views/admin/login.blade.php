@extends('layouts.admin', ['bare' => true])

@section('title', 'Admin Login · Med Alert')

@section('content')
    {{-- Uses the same ids as the header control so the shared toggle script picks it up. --}}
    <div class="fixed right-4 top-4">
        <button type="button" id="theme-toggle"
                class="relative flex h-9 w-9 items-center justify-center rounded-xl border border-line bg-card text-muted transition hover:border-accent/40 hover:text-accent"
                aria-label="Toggle dark mode" title="Toggle dark mode">
            <svg id="icon-sun" class="absolute h-[18px] w-[18px] transition-all duration-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="4"/>
                <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
            </svg>
            <svg id="icon-moon" class="absolute h-[18px] w-[18px] transition-all duration-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
        </button>
    </div>

    <div class="rise mb-8 text-center">
        <div class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-accent to-accent2 text-xl font-bold text-white shadow-xl shadow-accent/25">M</div>
        <h1 class="text-2xl font-bold tracking-tight text-ink">Med Alert</h1>
        <p class="mt-1 text-sm text-muted">Sign in to the admin panel</p>
    </div>

    <div class="rise rounded-2xl border border-line bg-card p-6 shadow-xl shadow-black/5 sm:p-8" style="--delay: 100ms">
        <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username"
                       class="w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:outline-none focus:ring-2 focus:ring-accent/30
                              {{ $errors->has('email') ? 'border-danger' : 'border-line focus:border-accent' }}"
                       placeholder="admin@medalert.com">
                @error('email')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password"
                       class="w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted transition focus:outline-none focus:ring-2 focus:ring-accent/30
                              {{ $errors->has('password') ? 'border-danger' : 'border-line focus:border-accent' }}"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remember" value="1"
                       class="h-4 w-4 rounded border-line text-accent focus:ring-accent/30">
                Remember me
            </label>

            <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-accent to-accent2 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent/25 transition hover:opacity-90 hover:shadow-xl hover:shadow-accent/30 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:ring-offset-2">
                Sign in
            </button>
        </form>
    </div>

    <p class="rise mt-6 text-center text-sm text-muted" style="--delay: 200ms">
        <a href="{{ route('order.create') }}" class="font-medium text-accent transition hover:underline">Back to order form</a>
    </p>
@endsection
