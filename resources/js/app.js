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
