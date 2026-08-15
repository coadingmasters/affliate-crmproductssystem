<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', 'Med Alert')</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    surface:  'rgb(var(--surface) / <alpha-value>)',
                    card:     'rgb(var(--card) / <alpha-value>)',
                    elevated: 'rgb(var(--elevated) / <alpha-value>)',
                    line:     'rgb(var(--line) / <alpha-value>)',
                    ink:      'rgb(var(--ink) / <alpha-value>)',
                    muted:    'rgb(var(--muted) / <alpha-value>)',
                    brand:    'rgb(var(--brand) / <alpha-value>)',
                    brand2:   'rgb(var(--brand2) / <alpha-value>)',
                    success:  'rgb(var(--success) / <alpha-value>)',
                    danger:   'rgb(var(--danger) / <alpha-value>)',
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
     | Storefront palette — royal blue on a cool neutral base.
     | Follows the viewer's OS theme automatically.
     */
    :root {
        --surface:  246 248 252;
        --card:     255 255 255;
        --elevated: 240 244 251;
        --line:     221 229 241;
        --ink:      12 22 41;
        --muted:    99 114 140;
        --brand:    29 78 216;
        --brand2:   59 130 246;
        --success:  22 163 74;
        --danger:   220 38 38;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --surface:  8 13 25;
            --card:     16 24 42;
            --elevated: 24 34 56;
            --line:     35 48 75;
            --ink:      228 236 248;
            --muted:    139 152 178;
            --brand:    59 130 246;
            --brand2:   96 165 250;
            --success:  74 222 128;
            --danger:   248 113 113;
        }
    }

    html { color-scheme: light dark; }

    body {
        background-color: rgb(var(--surface));
        position: relative;
        overflow-x: hidden;
    }

    /* Soft brand glows behind the card — decorative only. */
    .orb {
        position: fixed;
        border-radius: 9999px;
        filter: blur(72px);
        opacity: .5;
        pointer-events: none;
        z-index: 0;
    }

    .orb-a {
        width: 420px; height: 420px;
        top: -140px; left: -120px;
        background: rgb(var(--brand) / .30);
        animation: float-a 18s ease-in-out infinite;
    }

    .orb-b {
        width: 380px; height: 380px;
        bottom: -150px; right: -110px;
        background: rgb(var(--brand2) / .26);
        animation: float-b 22s ease-in-out infinite;
    }

    @keyframes float-a {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50%      { transform: translate(40px, 50px) scale(1.12); }
    }

    @keyframes float-b {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50%      { transform: translate(-45px, -35px) scale(1.1); }
    }

    /* ---------- Entrance ---------- */
    @keyframes rise {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: none; }
    }

    .rise {
        opacity: 0;
        animation: rise .6s cubic-bezier(.22, 1, .36, 1) forwards;
        animation-delay: var(--delay, 0ms);
    }

    @keyframes pop {
        0%   { opacity: 0; transform: scale(.94) translateY(-8px); }
        60%  { transform: scale(1.02); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }

    .pop { animation: pop .5s cubic-bezier(.22, 1, .36, 1) forwards; }

    /* ---------- Fields ---------- */
    .field {
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .field:focus {
        outline: none;
        border-color: rgb(var(--brand));
        box-shadow: 0 0 0 4px rgb(var(--brand) / .14);
    }

    .field:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    /* Total flashes when the figure changes */
    @keyframes flash {
        0%   { transform: scale(1); }
        40%  { transform: scale(1.06); color: rgb(var(--brand2)); }
        100% { transform: scale(1); }
    }

    .flash { animation: flash .45s ease; }

    /* ---------- Submit button ---------- */
    .cta {
        position: relative;
        overflow: hidden;
        transition: transform .2s cubic-bezier(.22, 1, .36, 1), box-shadow .25s ease;
    }

    .cta:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px -12px rgb(var(--brand) / .55);
    }

    .cta:active:not(:disabled) { transform: translateY(0); }
    .cta:disabled { opacity: .7; cursor: wait; }

    /* Sheen sweep on hover */
    .cta::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(110deg, transparent 30%, rgb(255 255 255 / .28) 50%, transparent 70%);
        transform: translateX(-120%);
        transition: transform .7s ease;
    }

    .cta:hover:not(:disabled)::after { transform: translateX(120%); }

    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { animation: spin .7s linear infinite; }

    @media (prefers-reduced-motion: reduce) {
        .rise, .pop, .flash, .orb-a, .orb-b, .spin {
            animation: none !important;
            opacity: 1 !important;
            transform: none !important;
        }
        .cta:hover:not(:disabled) { transform: none; }
        .cta::after { display: none; }
    }
</style>
