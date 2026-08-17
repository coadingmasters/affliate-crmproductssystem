{{-- Reusable dialog used instead of the browser's alert() / confirm().
     Usage from any page:
       Modal.confirm({ title, message, confirmText, tone, onConfirm })
       Modal.alert({ title, message })
     Any form with data-confirm="..." is intercepted automatically. --}}

<div id="app-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="app-modal-title">
    <div id="app-modal-backdrop" class="absolute inset-0 bg-ink/50 opacity-0 backdrop-blur-sm transition-opacity duration-200"></div>

    <div id="app-modal-card"
         class="relative w-full max-w-md scale-95 overflow-hidden rounded-2xl border border-line bg-card opacity-0 shadow-2xl transition-all duration-200 sm:max-w-lg">

        <div class="flex items-start gap-4 p-5 sm:p-6">
            <span id="app-modal-icon"
                  class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-danger/10 text-danger">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.71-3L13.71 4a2 2 0 00-3.42 0L3.36 16a2 2 0 001.71 3z"/>
                </svg>
            </span>

            <div class="min-w-0 flex-1 pt-0.5">
                <h2 id="app-modal-title" class="text-base font-semibold text-ink">Are you sure?</h2>
                <p id="app-modal-message" class="mt-1.5 text-sm leading-relaxed text-muted">This action cannot be undone.</p>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-line bg-elevated px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
            <button type="button" id="app-modal-cancel"
                    class="w-full rounded-xl border border-line bg-card px-4 py-2.5 text-sm font-medium text-ink transition hover:bg-elevated sm:w-auto">
                Cancel
            </button>
            <button type="button" id="app-modal-confirm"
                    class="w-full rounded-xl bg-danger px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 sm:w-auto">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
window.Modal = (function () {
    const root = document.getElementById('app-modal');
    const backdrop = document.getElementById('app-modal-backdrop');
    const card = document.getElementById('app-modal-card');
    const iconWrap = document.getElementById('app-modal-icon');
    const titleEl = document.getElementById('app-modal-title');
    const messageEl = document.getElementById('app-modal-message');
    const cancelBtn = document.getElementById('app-modal-cancel');
    const confirmBtn = document.getElementById('app-modal-confirm');

    let onConfirm = null;
    let lastFocused = null;

    const TONES = {
        danger: { wrap: 'bg-danger/10 text-danger', button: 'bg-danger' },
        brand:  { wrap: 'bg-brand/10 text-brand',   button: 'bg-brand'  },
        warning:{ wrap: 'bg-warning/10 text-warning', button: 'bg-warning' },
    };

    function open(options) {
        const tone = TONES[options.tone] || TONES.danger;

        titleEl.textContent = options.title || 'Are you sure?';
        messageEl.textContent = options.message || '';
        confirmBtn.textContent = options.confirmText || 'Confirm';
        cancelBtn.textContent = options.cancelText || 'Cancel';
        cancelBtn.classList.toggle('hidden', options.alertOnly === true);

        iconWrap.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-full ' + tone.wrap;
        confirmBtn.className = 'w-full rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 sm:w-auto ' + tone.button;

        onConfirm = options.onConfirm || null;
        lastFocused = document.activeElement;

        root.classList.remove('hidden');
        root.classList.add('flex');

        // next frame so the transition actually runs
        requestAnimationFrame(function () {
            backdrop.classList.add('opacity-100');
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        });

        confirmBtn.focus();
        document.body.style.overflow = 'hidden';
    }

    function close() {
        backdrop.classList.remove('opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        card.classList.remove('scale-100', 'opacity-100');
        document.body.style.overflow = '';

        setTimeout(function () {
            root.classList.add('hidden');
            root.classList.remove('flex');
            if (lastFocused) lastFocused.focus();
        }, 200);
    }

    cancelBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    confirmBtn.addEventListener('click', function () {
        const action = onConfirm;
        close();
        if (action) action();
    });

    document.addEventListener('keydown', function (event) {
        if (root.classList.contains('hidden')) return;
        if (event.key === 'Escape') close();
        // keep focus inside the dialog
        if (event.key === 'Tab') {
            const focusable = [cancelBtn, confirmBtn].filter(el => !el.classList.contains('hidden'));
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });

    // Any form carrying data-confirm asks first, then submits for real.
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === 'yes') return;

        event.preventDefault();
        open({
            title: form.dataset.confirmTitle || 'Are you sure?',
            message: form.dataset.confirm,
            confirmText: form.dataset.confirmText || 'Delete',
            tone: form.dataset.confirmTone || 'danger',
            onConfirm: function () {
                form.dataset.confirmed = 'yes';
                form.submit();
            },
        });
    }, true);

    return {
        confirm: open,
        alert: function (options) {
            open(Object.assign({ alertOnly: true, confirmText: 'OK', tone: 'warning' }, options));
        },
        close: close,
    };
})();
</script>
