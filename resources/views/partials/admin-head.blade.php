<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', 'Med Alert Admin')</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>

{{-- Apply the saved theme before first paint so the page never flashes.
     Light is the default; dark is opt-in via the header toggle. --}}
<script>
    if (localStorage.getItem('admin-theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }
</script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    surface:  'rgb(var(--surface) / <alpha-value>)',
                    card:     'rgb(var(--card) / <alpha-value>)',
                    elevated: 'rgb(var(--elevated) / <alpha-value>)',
                    line:     'rgb(var(--line) / <alpha-value>)',
                    ink:      'rgb(var(--ink) / <alpha-value>)',
                    muted:    'rgb(var(--muted) / <alpha-value>)',
                    // brand and accent are the same colour so admin and storefront match
                    brand:    'rgb(var(--brand) / <alpha-value>)',
                    brand2:   'rgb(var(--brand2) / <alpha-value>)',
                    accent:   'rgb(var(--brand) / <alpha-value>)',
                    accent2:  'rgb(var(--brand2) / <alpha-value>)',
                    success:  'rgb(var(--success) / <alpha-value>)',
                    warning:  'rgb(var(--warning) / <alpha-value>)',
                    info:     'rgb(var(--info) / <alpha-value>)',
                    danger:   'rgb(var(--danger) / <alpha-value>)',
                    sidebar:  'rgb(var(--sidebar) / <alpha-value>)',
                },
                fontFamily: {
                    sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                },
            },
        },
    };
</script>

<style>
    /*
     | Same palette as the storefront — cool slate with a coral accent.
     | Light only, by design.
     */
    :root {
        --surface:  248 249 251;
        --card:     255 255 255;
        --elevated: 241 244 249;
        --line:     223 228 238;
        --ink:      15 21 33;
        --muted:    100 112 133;
        --brand:    194 65 12;
        --brand2:   234 88 12;
        --success:  21 128 61;
        --warning:  180 83 9;
        --info:     3 105 161;
        --danger:   190 24 60;
        --sidebar:  23 31 47;
    }

    /*
     | Optional dark theme. Light is the default; this only applies when the
     | admin turns it on with the header toggle.
     */
    html.dark {
        --surface:  12 15 20;
        --card:     22 26 34;
        --elevated: 31 37 48;
        --line:     44 52 67;
        --ink:      232 236 244;
        --muted:    148 160 180;
        --brand:    251 146 60;
        --brand2:   253 186 116;
        --success:  74 222 128;
        --warning:  251 191 36;
        --info:     56 189 248;
        --danger:   248 113 113;
        --sidebar:  15 19 26;
    }

    html { color-scheme: light; }
    html.dark { color-scheme: dark; }

    body { transition: background-color .3s ease, color .3s ease; }

    /* ---------- Entrance animations ---------- */
    @keyframes rise {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: none; }
    }

    .rise {
        opacity: 0;
        animation: rise .55s cubic-bezier(.22, 1, .36, 1) forwards;
        animation-delay: var(--delay, 0ms);
    }

    @keyframes draw { to { stroke-dashoffset: 0; } }

    .draw {
        stroke-dasharray: var(--len);
        stroke-dashoffset: var(--len);
        animation: draw 1.4s cubic-bezier(.4, 0, .2, 1) forwards;
        animation-delay: var(--delay, 200ms);
    }

    @keyframes fadeIn { to { opacity: 1; } }

    .fade-in {
        opacity: 0;
        animation: fadeIn .8s ease forwards;
        animation-delay: var(--delay, 600ms);
    }

    @keyframes grow { from { transform: scaleX(0); } to { transform: scaleX(1); } }

    .grow {
        transform-origin: left center;
        animation: grow .9s cubic-bezier(.22, 1, .36, 1) forwards;
        animation-delay: var(--delay, 300ms);
    }

    /* ---------- Interaction ---------- */
    .lift {
        transition: transform .25s cubic-bezier(.22, 1, .36, 1), box-shadow .25s ease, border-color .25s ease;
    }

    .lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px -12px rgb(var(--ink) / .18);
        border-color: rgb(var(--brand) / .35);
    }

    .row-hover { transition: background-color .18s ease; }

    .nav-link { position: relative; transition: color .2s ease, background-color .2s ease; }

    .nav-link::before {
        content: '';
        position: absolute;
        left: 0; top: 50%;
        width: 3px; height: 0;
        border-radius: 0 3px 3px 0;
        background: rgb(var(--brand2));
        transform: translateY(-50%);
        transition: height .25s ease;
    }

    .nav-link:hover::before { height: 55%; }
    .nav-link.is-active::before { height: 70%; }

    * { scrollbar-width: thin; scrollbar-color: rgb(var(--line)) transparent; }
    *::-webkit-scrollbar { width: 10px; height: 10px; }
    *::-webkit-scrollbar-track { background: transparent; }
    *::-webkit-scrollbar-thumb {
        background: rgb(var(--line));
        border-radius: 8px;
        border: 3px solid transparent;
        background-clip: content-box;
    }
    *::-webkit-scrollbar-thumb:hover { background: rgb(var(--muted) / .5); background-clip: content-box; }

    @media (prefers-reduced-motion: reduce) {
        .rise, .draw, .fade-in, .grow {
            animation: none !important;
            opacity: 1 !important;
            stroke-dashoffset: 0 !important;
            transform: none !important;
        }
        .lift:hover { transform: none; }
    }
</style>
