document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('confirmDeleteModal');
    if (!modal) {
        return;
    }

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        const message = button.getAttribute('data-delete-message');
        const url = button.getAttribute('data-delete-url');
        const token = button.getAttribute('data-delete-token');

        // Message
        const messageEl = document.getElementById('confirmDeleteMessage');
        if (messageEl && message) {
            messageEl.textContent = message;
        }

        // Form action
        const form = document.getElementById('confirmDeleteForm');
        if (form && url) {
            form.action = url;
        }

        // CSRF
        const tokenInput = document.getElementById('confirmDeleteToken');
        if (tokenInput && token) {
            tokenInput.value = token;
        }
    });
});
