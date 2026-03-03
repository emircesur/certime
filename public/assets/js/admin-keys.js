document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('generate-keys-form');
    if (!form) return;

    // Toast container
    let toastContainer = document.getElementById('admin-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'admin-toast-container';
        toastContainer.style.position = 'fixed';
        toastContainer.style.top = '1rem';
        toastContainer.style.right = '1rem';
        toastContainer.style.zIndex = 1080;
        document.body.appendChild(toastContainer);
    }

    function showToast(title, message, type = 'primary', timeout = 4000) {
        const id = 't' + Math.random().toString(36).slice(2, 9);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>`;
        toastContainer.appendChild(wrapper);
        const toastEl = wrapper.querySelector('.toast');
        const bsToast = new bootstrap.Toast(toastEl, { delay: timeout });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => wrapper.remove());
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const uid = form.querySelector('input[name="credential_uid"]').value;
        const encrypt = form.querySelector('input[name="encrypt"]').value || '0';

        const data = new FormData();
        data.append('encrypt', encrypt);

        fetch('/admin/keys/generate', {
            method: 'POST',
            body: data,
            credentials: 'same-origin'
        }).then(async (res) => {
            const ctype = res.headers.get('content-type') || '';
            if (ctype.indexOf('application/json') !== -1) {
                const json = await res.json();
                if (json.success) {
                    showToast('Success', json.message || 'Keys generated', 'success');
                    // redirect to signed download
                    setTimeout(() => { window.location = '/download/pdf/' + encodeURIComponent(uid) + '?signed=1'; }, 800);
                } else {
                    showToast('Error', json.message || 'Failed to generate keys', 'danger');
                }
            } else {
                // If server returned HTML, open it for more info
                const text = await res.text();
                const w = window.open();
                w.document.write(text);
            }
        }).catch(err => {
            showToast('Error', 'Network or server error while generating keys', 'danger');
            console.error(err);
        });
    });
});
