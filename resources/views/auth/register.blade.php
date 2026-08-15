@extends('layouts.app')

@section('title', 'Create Account · Med Alert')

@php
    $input = 'field w-full rounded-xl border bg-elevated px-3.5 py-2.5 text-sm text-ink placeholder-muted';
@endphp

@section('content')
    <div class="mx-auto w-full max-w-lg">

        <div class="rise mb-7 text-center">
            <span class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand2 text-white shadow-xl shadow-brand/30">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight text-ink">Create your account</h1>
            <p class="mt-1.5 text-sm text-muted">It only takes a moment</p>
        </div>

        <div class="rise rounded-2xl border border-line bg-card/90 p-6 shadow-2xl shadow-black/5 backdrop-blur-xl sm:p-8" style="--delay: 90ms">
            <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-ink">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                           autocomplete="name" placeholder="John Smith"
                           class="{{ $input }} {{ $errors->has('name') ? 'border-danger' : 'border-line' }}">
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           autocomplete="email" placeholder="you@example.com"
                           class="{{ $input }} {{ $errors->has('email') ? 'border-danger' : 'border-line' }}">
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Password</label>
                        <input type="password" name="password" id="password" required autocomplete="new-password"
                               placeholder="••••••••"
                               class="{{ $input }} {{ $errors->has('password') ? 'border-danger' : 'border-line' }}">
                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-ink">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               autocomplete="new-password" placeholder="••••••••"
                               class="{{ $input }} border-line">
                    </div>
                </div>

                <p class="text-xs text-muted">At least 8 characters, including a letter and a number.</p>

                <button type="submit"
                        class="cta flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand to-brand2 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand/30">
                    Create Account
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="rise mt-5 text-center" style="--delay: 180ms">
            <p class="text-sm text-muted">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-brand transition hover:underline">Sign in</a>
            </p>
        </div>

        <p class="rise mt-6 text-center text-xs text-muted" style="--delay: 240ms">
            &copy; {{ date('Y') }} Med Alert
        </p>
    </div>
@endsection
