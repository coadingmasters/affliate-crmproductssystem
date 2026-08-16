{{-- Shared header for the signed in customer area. --}}
<div class="rise mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-card/80 px-4 py-2.5 backdrop-blur">
    <div class="flex min-w-0 items-center gap-2.5">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand to-brand2 text-xs font-bold text-white">
            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
        </span>
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
        </div>
    </div>

    <div class="flex shrink-0 flex-wrap items-center gap-2">
        @php $onHistory = request()->routeIs('order.history'); @endphp

        <a href="{{ route('order.create') }}"
           class="rounded-lg px-3 py-1.5 text-xs font-semibold transition
                  {{ $onHistory ? 'border border-line text-muted hover:border-brand hover:text-brand' : 'bg-brand/10 text-brand' }}">
            Place Order
        </a>

        <a href="{{ route('order.history') }}"
           class="rounded-lg px-3 py-1.5 text-xs font-semibold transition
                  {{ $onHistory ? 'bg-brand/10 text-brand' : 'border border-line text-muted hover:border-brand hover:text-brand' }}">
            My Orders
        </a>

        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}"
               class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:border-brand hover:text-brand">
                Admin
            </a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:border-danger hover:text-danger">
                Sign out
            </button>
        </form>
    </div>
</div>
