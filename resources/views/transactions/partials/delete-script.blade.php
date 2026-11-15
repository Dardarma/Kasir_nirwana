<script>
    $(function () {
        const form = document.getElementById('transaction-delete-form');
        if (!form) return;

        const input = form.elements.namedItem('konfirmasi_hapus');
        const button = form.querySelector('button[type="submit"]');
        const code = document.getElementById('transaction-delete-code');
        const openButton = document.getElementById('transaction-delete-open');
        const detailModal = $('#detail');
        const deleteModal = $('#transaction-delete-modal');
        let detailTrigger;
        const matches = () => Boolean(form.getAttribute('action')) && input.value.trim() === form.dataset.phrase;

        detailModal.on('show.bs.modal', function (event) {
            detailTrigger = event.relatedTarget;
            openButton.hidden = !detailTrigger?.dataset.deleteUrl;
            openButton.disabled = true;
        });
        detailModal.on('shown.bs.modal', () => {
            openButton.disabled = false;
        });

        openButton.addEventListener('click', () => {
            const url = detailTrigger?.dataset.deleteUrl;
            if (!url) return;

            form.reset();
            button.disabled = true;
            form.setAttribute('action', url);
            code.textContent = detailTrigger.dataset.deleteCode;

            detailModal.one('hidden.bs.modal', () => deleteModal.modal('show'));
            detailModal.modal('hide');
        });

        deleteModal.on('shown.bs.modal', () => input.focus());
        deleteModal.on('hidden.bs.modal', () => {
            form.removeAttribute('action');
            detailModal.modal('show', detailTrigger);
        });

        input.addEventListener('input', () => {
            button.disabled = !matches();
        });
        form.addEventListener('submit', (event) => {
            if (!matches()) event.preventDefault();
        });
    });
</script>
