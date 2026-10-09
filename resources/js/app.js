// Ask for confirmation before submitting any form marked with data-confirm,
// e.g. <form method="POST" data-confirm="Recall this batch?">.
// Server-side authorisation and validation still apply regardless.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    if (!window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});

// Show / hide password toggles: <button data-password-toggle="input-id">.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-password-toggle]');

    if (!button) {
        return;
    }

    const input = document.getElementById(button.dataset.passwordToggle);

    if (!input) {
        return;
    }

    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.setAttribute('aria-pressed', String(show));
    button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    button.querySelector('[data-icon="show"]')?.classList.toggle('hidden', show);
    button.querySelector('[data-icon="hide"]')?.classList.toggle('hidden', !show);
    input.focus();
});
