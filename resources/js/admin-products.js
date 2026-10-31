const dialog = document.querySelector('#delete-confirmation');

if (dialog) {
    let pendingForm = null;
    let confirmed = false;
    document.querySelectorAll('[data-delete-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (confirmed && pendingForm === form) return;
            event.preventDefault();
            pendingForm = form;
            dialog.querySelector('[data-delete-name]').textContent = form.dataset.objectName;
            dialog.showModal();
        });
    });
    dialog.querySelector('[data-delete-cancel]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-delete-confirm]').addEventListener('click', () => {
        if (!pendingForm || confirmed) return;
        confirmed = true;
        dialog.querySelector('[data-delete-confirm]').disabled = true;
        pendingForm.requestSubmit();
    });
    dialog.addEventListener('close', () => { if (!confirmed) pendingForm = null; });
}
