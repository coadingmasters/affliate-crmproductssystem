{{-- Drives the "Submitted by" multi select. Shared by the orders list and the
     dashboard, so the two behave identically. --}}
<script>
    (function () {
        const wrap = document.getElementById('user-picker');

        if (!wrap) {
            return;
        }

        // Whichever filter form the picker happens to sit inside.
        const form = wrap.closest('form');
        const toggle = document.getElementById('user-picker-toggle');
        const panel = document.getElementById('user-picker-panel');
        const label = document.getElementById('user-picker-label');
        const search = document.getElementById('user-picker-search');
        const empty = document.getElementById('user-picker-empty');
        const options = Array.from(wrap.querySelectorAll('.user-option'));
        const boxes = Array.from(wrap.querySelectorAll('.user-checkbox'));

        function describe() {
            const chosen = boxes.filter(b => b.checked);

            if (chosen.length === 0) {
                label.textContent = 'All users';
            } else if (chosen.length === 1) {
                label.textContent = chosen[0].closest('.user-option').querySelector('span span').textContent.trim();
            } else {
                label.textContent = chosen.length + ' users selected';
            }
        }

        function open() {
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            search.value = '';
            options.forEach(o => o.classList.remove('hidden'));
            empty.classList.add('hidden');
            search.focus();
        }

        function close() {
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', function () {
            panel.classList.contains('hidden') ? open() : close();
        });

        document.addEventListener('click', function (event) {
            if (!wrap.contains(event.target)) {
                close();
            }
        });

        search.addEventListener('input', function () {
            const term = search.value.trim().toLowerCase();
            let visible = 0;

            options.forEach(function (option) {
                const match = !term || option.dataset.name.includes(term);
                option.classList.toggle('hidden', !match);
                if (match) visible++;
            });

            empty.classList.toggle('hidden', visible > 0);
        });

        boxes.forEach(box => box.addEventListener('change', describe));

        document.getElementById('user-picker-clear').addEventListener('click', function () {
            boxes.forEach(b => { b.checked = false; });
            describe();
            form.submit();
        });

        document.getElementById('user-picker-apply').addEventListener('click', function () {
            form.submit();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
                close();
            }
        });

        describe();
    })();
</script>
