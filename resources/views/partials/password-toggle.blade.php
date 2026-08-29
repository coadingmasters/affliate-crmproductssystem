{{-- Drives every [data-password-toggle] button on the page. --}}
<script>
    (function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            const field = document.getElementById(button.dataset.passwordToggle);

            if (!field) {
                return;
            }

            const open = button.querySelector('[data-icon="open"]');
            const shut = button.querySelector('[data-icon="shut"]');

            button.addEventListener('click', function () {
                const revealed = field.type === 'text';

                field.type = revealed ? 'password' : 'text';
                button.setAttribute('aria-pressed', revealed ? 'false' : 'true');
                button.setAttribute('aria-label', revealed ? 'Show password' : 'Hide password');

                open.classList.toggle('hidden', !revealed);
                shut.classList.toggle('hidden', revealed);

                // Typing should carry on where it left off.
                const caret = field.value.length;
                field.focus();
                field.setSelectionRange(caret, caret);
            });
        });
    })();
</script>
