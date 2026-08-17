@php $onHistory = request()->routeIs('order.history'); @endphp

{{-- Header for the customer area. Customers only — admins never reach these pages.
     Stacks on phones, single row from sm upwards. --}}
<div class="rise mb-4 rounded-2xl border border-line bg-card/80 p-2.5 backdrop-blur sm:flex sm:items-center sm:justify-between sm:gap-3 sm:px-4">

    <div class="flex min-w-0 items-center gap-2.5 px-1 sm:px-0">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand to-brand2 text-xs font-bold text-white sm:h-8 sm:w-8">
            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
        </span>
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
        </div>
    </div>

    {{-- Full width segmented control on phones, inline buttons on larger screens --}}
    <nav class="mt-2.5 grid grid-cols-3 gap-2 border-t border-line pt-2.5
                sm:mt-0 sm:flex sm:shrink-0 sm:items-center sm:gap-2 sm:border-0 sm:pt-0">
        <a href="{{ route('order.create') }}"
           class="rounded-lg px-3 py-2 text-center text-xs font-semibold transition sm:py-1.5
                  {{ $onHistory
                      ? 'border border-line text-muted hover:border-brand hover:text-brand'
                      : 'bg-brand/10 text-brand' }}">
            Place Order
        </a>

        <a href="{{ route('order.history') }}"
           class="rounded-lg px-3 py-2 text-center text-xs font-semibold transition sm:py-1.5
                  {{ $onHistory
                      ? 'bg-brand/10 text-brand'
                      : 'border border-line text-muted hover:border-brand hover:text-brand' }}">
            My Dashboard
        </a>

        <form method="POST" action="{{ route('logout') }}" class="contents sm:block">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg border border-line px-3 py-2 text-xs font-medium text-muted transition hover:border-danger hover:text-danger sm:py-1.5">
                Sign out
            </button>
        </form>
    </nav>
</div>
