/**
 * CertiMe — Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // --- CSRF Token ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });

    // --- Confirm dialogs for dangerous actions ---
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // --- Copy to clipboard utility ---
    window.copyToClipboard = function(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            if (btn) {
                const orig = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-rounded" style="font-size:18px">check</span>';
                setTimeout(() => btn.innerHTML = orig, 2000);
            }
        }).catch(() => {
            // Fallback
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        });
    };

    // --- Material ripple on buttons ---
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            this.style.setProperty('--ripple-x', x + 'px');
            this.style.setProperty('--ripple-y', y + 'px');
        });
    });

    // --- Smooth scroll for anchors ---
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // --- Tooltip initialization ---
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // --- Admin: Role change feedback ---
    document.querySelectorAll('select[name="role"]').forEach(select => {
        select.addEventListener('change', function() {
            if (confirm('Change this user\'s role to ' + this.value + '?')) {
                this.closest('form').submit();
            } else {
                // Reset to original
                this.value = this.querySelector('[selected]')?.value || this.options[0].value;
            }
        });
    });

    console.log('%c CertiMe %c Digital Credentialing Platform ', 
        'background: #6750a4; color: white; padding: 4px 8px; border-radius: 4px 0 0 4px; font-weight: bold;',
        'background: #eaddff; color: #21005d; padding: 4px 8px; border-radius: 0 4px 4px 0;'
    );
});
