<!-- NetSaz Custom Confirmation, Alert, Prompt & Toast Overlay Component -->
<div id="netsaz-confirm-overlay" class="netsaz-overlay-backdrop" style="display:none;" aria-hidden="true">
    <div class="netsaz-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="netsaz-modal-title">
        <div class="netsaz-modal-content">
            <!-- Icon -->
            <div id="netsaz-modal-icon-wrap" class="netsaz-modal-icon-wrap icon-primary">
                <div id="netsaz-modal-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
            </div>

            <!-- Title & Message -->
            <h3 id="netsaz-modal-title" class="netsaz-modal-title">Konfirmasi Tindakan</h3>
            <p id="netsaz-modal-message" class="netsaz-modal-message">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>

            <!-- Optional Prompt Input -->
            <div id="netsaz-modal-input-wrap" style="display:none;margin-bottom:20px;text-align:left;">
                <label id="netsaz-modal-input-label" style="display:block;font-size:12.5px;font-weight:500;color:#94a3b8;margin-bottom:6px;">Keterangan</label>
                <input type="text" id="netsaz-modal-input" style="width:100%;box-sizing:border-box;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:8px;padding:10px 14px;color:#fff;font-size:13.5px;font-family:'Inter',sans-serif;outline:none;transition:border-color .15s;" placeholder="">
            </div>

            <!-- Actions -->
            <div class="netsaz-modal-actions">
                <button type="button" id="netsaz-modal-btn-cancel" class="netsaz-btn-cancel">
                    Batal
                </button>
                <button type="button" id="netsaz-modal-btn-confirm" class="netsaz-btn-confirm btn-primary">
                    <span id="netsaz-modal-btn-confirm-text">Ya, Lanjutkan</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- NetSaz Toast Container -->
<div id="netsaz-toast-container" class="netsaz-toast-container" aria-live="polite"></div>

<style>
/* NetSaz Overlay Styles */
.netsaz-overlay-backdrop {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(3, 7, 18, 0.78);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.netsaz-overlay-backdrop.active {
    opacity: 1;
    pointer-events: auto;
}
.netsaz-modal-dialog {
    background: #0B1120;
    background: linear-gradient(180deg, #0F172A 0%, #0B1120 100%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.85), 0 0 0 1px rgba(255, 255, 255, 0.05);
    border-radius: 18px;
    width: 100%;
    max-width: 440px;
    overflow: hidden;
    transform: scale(0.92) translateY(12px);
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.netsaz-overlay-backdrop.active .netsaz-modal-dialog {
    transform: scale(1) translateY(0);
}
.netsaz-modal-content {
    padding: 28px 24px 22px;
    text-align: center;
}
.netsaz-modal-icon-wrap {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.netsaz-modal-icon-wrap.icon-success {
    background: rgba(0, 214, 143, 0.14);
    border: 1px solid rgba(0, 214, 143, 0.35);
    color: #00D68F;
    box-shadow: 0 0 25px rgba(0, 214, 143, 0.2);
}
.netsaz-modal-icon-wrap.icon-danger {
    background: rgba(239, 68, 68, 0.14);
    border: 1px solid rgba(239, 68, 68, 0.35);
    color: #EF4444;
    box-shadow: 0 0 25px rgba(239, 68, 68, 0.2);
}
.netsaz-modal-icon-wrap.icon-warning {
    background: rgba(245, 158, 11, 0.14);
    border: 1px solid rgba(245, 158, 11, 0.35);
    color: #F59E0B;
    box-shadow: 0 0 25px rgba(245, 158, 11, 0.2);
}
.netsaz-modal-icon-wrap.icon-primary,
.netsaz-modal-icon-wrap.icon-info {
    background: rgba(59, 130, 246, 0.14);
    border: 1px solid rgba(59, 130, 246, 0.35);
    color: #3B82F6;
    box-shadow: 0 0 25px rgba(59, 130, 246, 0.2);
}

.netsaz-modal-title {
    font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 19px;
    font-weight: 700;
    color: #F8FAFC;
    margin: 0 0 10px;
    line-height: 1.3;
}
.netsaz-modal-message {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 14px;
    line-height: 1.55;
    color: #94A3B8;
    margin: 0 0 20px;
    word-break: break-word;
}
.netsaz-modal-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: center;
}
.netsaz-btn-cancel {
    flex: 1;
    padding: 11px 18px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 9px;
    color: #E2E8F0;
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: center;
}
.netsaz-btn-cancel:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
}
.netsaz-btn-confirm {
    flex: 1;
    padding: 11px 18px;
    border-radius: 9px;
    border: none;
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.netsaz-btn-confirm.btn-success,
.netsaz-btn-confirm.btn-primary {
    background: #00D68F;
    color: #05131C;
    box-shadow: 0 4px 14px rgba(0, 214, 143, 0.35);
}
.netsaz-btn-confirm.btn-success:hover,
.netsaz-btn-confirm.btn-primary:hover {
    background: #00BF7F;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 214, 143, 0.45);
}
.netsaz-btn-confirm.btn-danger {
    background: #EF4444;
    color: #FFFFFF;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
}
.netsaz-btn-confirm.btn-danger:hover {
    background: #DC2626;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(239, 68, 68, 0.45);
}
.netsaz-btn-confirm.btn-warning {
    background: #F59E0B;
    color: #0F172A;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
}
.netsaz-btn-confirm.btn-warning:hover {
    background: #D97706;
    transform: translateY(-1px);
}

/* Toast Styles */
.netsaz-toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 1000000;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
    max-width: 380px;
}
.netsaz-toast {
    background: rgba(15, 23, 42, 0.94);
    border: 1px solid rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 12px;
    padding: 12px 16px;
    color: #F8FAFC;
    font-family: 'Inter', sans-serif;
    font-size: 13.5px;
    line-height: 1.45;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    gap: 12px;
    pointer-events: auto;
    opacity: 0;
    transform: translateX(30px);
    transition: all 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}
.netsaz-toast.show {
    opacity: 1;
    transform: translateX(0);
}
.netsaz-toast-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.netsaz-toast-icon.success { color: #00D68F; }
.netsaz-toast-icon.danger { color: #EF4444; }
.netsaz-toast-icon.warning { color: #F59E0B; }
.netsaz-toast-icon.info { color: #3B82F6; }
</style>

<script>
(function() {
    // Icons SVGs
    const ICONS = {
        success: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>',
        danger: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>',
        warning: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        primary: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };

    const overlay = document.getElementById('netsaz-confirm-overlay');
    const titleEl = document.getElementById('netsaz-modal-title');
    const msgEl = document.getElementById('netsaz-modal-message');
    const iconWrap = document.getElementById('netsaz-modal-icon-wrap');
    const iconEl = document.getElementById('netsaz-modal-icon');
    const btnConfirm = document.getElementById('netsaz-modal-btn-confirm');
    const btnConfirmText = document.getElementById('netsaz-modal-btn-confirm-text');
    const btnCancel = document.getElementById('netsaz-modal-btn-cancel');
    const inputWrap = document.getElementById('netsaz-modal-input-wrap');
    const inputEl = document.getElementById('netsaz-modal-input');
    const inputLabel = document.getElementById('netsaz-modal-input-label');

    let currentResolver = null;
    let isPromptMode = false;

    window.NetSazModal = {
        confirm(opts = {}) {
            return new Promise((resolve) => {
                if (typeof opts === 'string') {
                    opts = { message: opts };
                }

                currentResolver = resolve;
                isPromptMode = false;
                inputWrap.style.display = 'none';

                const msg = opts.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                const lowerMsg = msg.toLowerCase();
                const defaultType = (lowerMsg.includes('hapus') || lowerMsg.includes('batalkan') || lowerMsg.includes('delete'))
                    ? 'danger'
                    : ((lowerMsg.includes('terima') || lowerMsg.includes('aktifkan') || lowerMsg.includes('simpan'))
                        ? 'success'
                        : 'primary');

                const type = opts.type || defaultType;

                titleEl.textContent = opts.title || (
                    type === 'danger' ? 'Konfirmasi Hapus' : 
                    (type === 'success' ? 'Konfirmasi Persetujuan' : 
                    (type === 'warning' ? 'Peringatan Tindakan' : 'Konfirmasi Tindakan'))
                );
                msgEl.textContent = msg;

                btnConfirmText.textContent = opts.confirmText || (
                    type === 'danger' ? 'Ya, Hapus' : 
                    (type === 'success' ? 'Ya, Lanjutkan' : 'Ya, Lanjutkan')
                );

                btnCancel.textContent = opts.cancelText || 'Batal';
                btnCancel.style.display = opts.isAlert ? 'none' : 'block';

                // Setup Type Icon & Button Classes
                iconWrap.className = 'netsaz-modal-icon-wrap icon-' + type;
                iconEl.innerHTML = ICONS[type] || ICONS.primary;

                btnConfirm.className = 'netsaz-btn-confirm btn-' + type;

                // Show modal
                overlay.style.display = 'flex';
                requestAnimationFrame(() => {
                    overlay.classList.add('active');
                });
                btnConfirm.focus();
            });
        },

        alert(opts = {}) {
            if (typeof opts === 'string') {
                opts = { message: opts };
            }
            opts.isAlert = true;
            opts.confirmText = opts.confirmText || 'Mengerti';
            return this.confirm(opts);
        },

        prompt(opts = {}) {
            return new Promise((resolve) => {
                if (typeof opts === 'string') {
                    opts = { message: opts };
                }

                currentResolver = resolve;
                isPromptMode = true;

                const msg = opts.message || 'Masukkan keterangan:';
                const type = opts.type || 'warning';

                titleEl.textContent = opts.title || 'Input Keterangan';
                msgEl.textContent = msg;

                inputWrap.style.display = 'block';
                inputLabel.textContent = opts.inputLabel || 'Keterangan:';
                inputEl.value = opts.defaultValue || '';
                inputEl.placeholder = opts.placeholder || 'Ketik di sini...';

                btnConfirmText.textContent = opts.confirmText || 'Kirim';
                btnCancel.textContent = opts.cancelText || 'Batal';
                btnCancel.style.display = 'block';

                iconWrap.className = 'netsaz-modal-icon-wrap icon-' + type;
                iconEl.innerHTML = ICONS[type] || ICONS.warning;
                btnConfirm.className = 'netsaz-btn-confirm btn-' + type;

                overlay.style.display = 'flex';
                requestAnimationFrame(() => {
                    overlay.classList.add('active');
                    inputEl.focus();
                });
            });
        },

        close(result = false) {
            overlay.classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
                inputWrap.style.display = 'none';
            }, 220);

            if (currentResolver) {
                if (isPromptMode) {
                    currentResolver(result ? inputEl.value : null);
                } else {
                    currentResolver(result);
                }
                currentResolver = null;
            }
        }
    };

    // NetSaz Toast Notifications
    const toastContainer = document.getElementById('netsaz-toast-container');
    window.NetSazToast = {
        show(message, type = 'success', duration = 3200) {
            if (!toastContainer) return;
            const toast = document.createElement('div');
            toast.className = 'netsaz-toast';

            const iconHtml = ICONS[type] || ICONS.info;
            toast.innerHTML = `
                <span class="netsaz-toast-icon ${type}">${iconHtml}</span>
                <span style="flex:1">${message}</span>
            `;

            toastContainer.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 250);
            }, duration);
        },
        success(msg) { this.show(msg, 'success'); },
        danger(msg) { this.show(msg, 'danger'); },
        error(msg) { this.show(msg, 'danger'); },
        warning(msg) { this.show(msg, 'warning'); },
        info(msg) { this.show(msg, 'info'); }
    };

    // Global replacements
    window.netsazConfirm = (msg, opts) => window.NetSazModal.confirm(typeof opts === 'object' ? { message: msg, ...opts } : msg);
    window.netsazAlert = (msg, opts) => window.NetSazModal.alert(typeof opts === 'object' ? { message: msg, ...opts } : msg);
    window.netsazPrompt = (msg, defaultVal, opts) => window.NetSazModal.prompt({ message: msg, defaultValue: defaultVal, ...(opts || {}) });

    // Replace native window.alert with NetSazToast to prevent native browser popups
    window.alert = function(msg) {
        window.NetSazToast.info(String(msg));
    };

    // Modal button events
    if (btnConfirm) {
        btnConfirm.addEventListener('click', () => window.NetSazModal.close(true));
    }
    if (btnCancel) {
        btnCancel.addEventListener('click', () => window.NetSazModal.close(false));
    }
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) window.NetSazModal.close(false);
        });
    }
    if (inputEl) {
        inputEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                window.NetSazModal.close(true);
            }
        });
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            window.NetSazModal.close(false);
        }
    });

    // Global Interceptor for Forms with data-confirm or legacy onsubmit confirm()
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || form._netsazConfirmed) {
            if (form) form._netsazConfirmed = false;
            return;
        }

        let confirmMsg = form.dataset.confirm || form.getAttribute('data-confirm');
        let title = form.dataset.confirmTitle || form.getAttribute('data-confirm-title');
        let type = form.dataset.confirmType || form.getAttribute('data-confirm-type');
        let confirmText = form.dataset.confirmText || form.getAttribute('data-confirm-text');
        let cancelText = form.dataset.cancelText || form.getAttribute('data-cancel-text');

        // Check if there is an onsubmit with confirm(...)
        const onsubmitAttr = form.getAttribute('onsubmit');
        if (!confirmMsg && onsubmitAttr && onsubmitAttr.includes('confirm(')) {
            const match = onsubmitAttr.match(/confirm\(\s*(['"])(.*?)\1\s*\)/s);
            if (match && match[2]) {
                confirmMsg = match[2].replace(/\\'/g, "'").replace(/\\"/g, '"');
                form.removeAttribute('onsubmit');
            }
        }

        if (confirmMsg) {
            e.preventDefault();
            e.stopPropagation();

            window.NetSazModal.confirm({
                title: title,
                message: confirmMsg,
                type: type,
                confirmText: confirmText,
                cancelText: cancelText
            }).then(accepted => {
                if (accepted) {
                    form._netsazConfirmed = true;
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
            });
        }
    }, true);

    // Global Interceptor for links/buttons with data-confirm
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-confirm]');
        if (!target || target.tagName === 'FORM') return;

        // If it's a submit button inside a form with data-confirm, form handler covers it
        if (target.type === 'submit' && target.form && target.form.hasAttribute('data-confirm')) {
            return;
        }

        const confirmMsg = target.dataset.confirm;
        if (!confirmMsg || target._netsazConfirmed) {
            target._netsazConfirmed = false;
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        window.NetSazModal.confirm({
            title: target.dataset.confirmTitle,
            message: confirmMsg,
            type: target.dataset.confirmType,
            confirmText: target.dataset.confirmText,
            cancelText: target.dataset.cancelText
        }).then(accepted => {
            if (accepted) {
                target._netsazConfirmed = true;
                if (target.tagName === 'A' && target.href) {
                    window.location.href = target.href;
                } else if (typeof target.click === 'function') {
                    target.click();
                }
            }
        });
    }, true);
})();
</script>
