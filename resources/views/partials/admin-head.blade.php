<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', 'Med Alert Admin')</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
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
                    accent:   'rgb(var(--accent) / <alpha-value>)',
                    accent2:  'rgb(var(--accent2) / <alpha-value>)',
                    success:  'rgb(var(--success) / <alpha-value>)',
                    warning:  'rgb(var(--warning) / <alpha-value>)',
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

{{-- Set the theme before first paint so the page never flashes the wrong one. --}}
<script>
    (function () {
        var stored = localStorage.getItem('admin-theme');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (stored === 'dark' || (!stored && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>

<style>
    /*
     | Admin design tokens — violet / cyan on a cool slate base.
     | Values are space separated RGB so Tailwind can apply opacity modifiers.
     */
    :root {
        --surface:  247 248 252;
        --card:     255 255 255;
        --elevated: 244 246 251;
        --line:     228 231 241;
        --ink:      12 17 32;
        --muted:    108 117 138;
        --accent:   109 76 255;
        --accent2:  6 182 212;
        --success:  16 185 129;
        --warning:  245 158 11;
        --danger:   244 63 94;
        --sidebar:  17 22 40;
    }

    html.dark {
        --surface:  9 12 22;
        --card:     19 25 42;
        --elevated: 26 33 54;
        --line:     37 46 71;
        --ink:      232 236 245;
        --muted:    138 148 170;
        --accent:   139 111 255;
        --accent2:  34 211 238;
        --success:  52 211 153;
        --warning:  251 191 36;
        --danger:   251 113 133;
        --sidebar:  13 17 32;
    }

    html { color-scheme: light; }
    html.dark { color-scheme: dark; }

    body {
        transition: background-color .3s ease, color .3s ease;
    }

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

    @keyframes draw {
        to { stroke-dashoffset: 0; }
    }

    .draw {
        stroke-dasharray: var(--len);
        stroke-dashoffset: var(--len);
        animation: draw 1.4s cubic-bezier(.4, 0, .2, 1) forwards;
        animation-delay: var(--delay, 200ms);
    }

    @keyframes fadeIn {
        to { opacity: 1; }
    }

    .fade-in {
        opacity: 0;
        animation: fadeIn .8s ease forwards;
        animation-delay: var(--delay, 600ms);
    }

    @keyframes grow {
        from { transform: scaleX(0); }
        to   { transform: scaleX(1); }
    }

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
        border-color: rgb(var(--accent) / .35);
    }

    .row-hover {
        transition: background-color .18s ease;
    }

    /* Sliding indicator on sidebar links */
    .nav-link {
        position: relative;
        transition: color .2s ease, background-color .2s ease;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 3px;
        height: 0;
        border-radius: 0 3px 3px 0;
        background: rgb(var(--accent2));
        transform: translateY(-50%);
        transition: height .25s ease;
    }

    .nav-link:hover::before { height: 55%; }
    .nav-link.is-active::before { height: 70%; }

    /* Custom scrollbars keep the dark theme from looking half finished */
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
        body { transition: none; }
    }
</style>
