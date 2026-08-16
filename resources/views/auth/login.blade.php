@extends('layouts.app')

@section('title', 'Sign In · Med Alert')

@php
    $input = 'field w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted';
@endphp

@section('content')
    <div class="mx-auto w-full max-w-md">

        <div class="rise mb-7 text-center">
            <span class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand2 text-white shadow-xl shadow-brand/30">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight text-ink">Welcome back</h1>
            <p class="mt-1.5 text-sm text-muted">Sign in to place your order</p>
        </div>

        @if (session('status'))
            <div class="pop mb-4 rounded-2xl border border-success/30 bg-success/10 px-4 py-3">
                <p class="text-sm font-medium text-success">{{ session('status') }}</p>
            </div>
        @endif

        <div class="rise rounded-2xl border border-line bg-card/90 p-6 shadow-2xl shadow-black/5 backdrop-blur-xl sm:p-8" style="--delay: 90ms">
            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           autocomplete="username" placeholder="you@example.com"
                           class="{{ $input }} {{ $errors->has('email') ? 'border-danger' : 'border-line' }}">
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Password</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                           placeholder="••••••••"
                           class="{{ $input }} {{ $errors->has('password') ? 'border-danger' : 'border-line' }}">
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                    <input type="checkbox" name="remember" value="1"
                           class="h-4 w-4 rounded border-line text-brand focus:ring-brand/30">
                    Keep me signed in
                </label>

                <button type="submit"
                        class="cta flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30">
                    Sign In
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="rise mt-5 rounded-xl border border-line bg-card/60 px-4 py-3 text-center backdrop-blur" style="--delay: 180ms">
            <p class="text-sm text-muted">
                Need an account? Accounts are issued by our team &mdash;
                <span class="font-medium text-ink">contact your administrator</span> for login details.
            </p>
        </div>

        <p class="rise mt-6 text-center text-xs text-muted" style="--delay: 240ms">
            &copy; {{ date('Y') }} Med Alert
        </p>
    </div>
@endsection
