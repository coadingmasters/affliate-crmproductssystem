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
                    accent:   'rgb(var(--brand) / <alpha-value>)',
                    accent2:  'rgb(var(--brand2) / <alpha-value>)',
                    success:  'rgb(var(--success) / <alpha-value>)',
                    warning:  'rgb(var(--warning) / <alpha-value>)',
                    info:     'rgb(var(--info) / <alpha-value>)',
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
     | Shared palette — cool slate base with a warm coral call to action.
     | Light only, by design: the whole product uses a white scheme.
     */
    :root {
        --surface:  248 249 251;
        --card:     255 255 255;
        --elevated: 241 244 249;
        --line:     223 228 238;
        --ink:      15 21 33;
        --muted:    100 112 133;
        --brand:    194 65 12;   /* deep coral — 5.18:1 on white, passes AA */
        --brand2:   234 88 12;   /* brighter coral for gradient ends */
        --success:  21 128 61;
        --warning:  180 83 9;
        --info:     3 105 161;
        --danger:   190 24 60;
    }

    html { color-scheme: light; }

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

    /* Smaller, lighter glows on phones — cheaper to paint and less overwhelming. */
    @media (max-width: 640px) {
        .orb { filter: blur(52px); opacity: .38; }
        .orb-a { width: 260px; height: 260px; top: -90px; left: -80px; }
        .orb-b { width: 240px; height: 240px; bottom: -90px; right: -70px; }
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
