import './bootstrap';
import * as Turbo from '@hotwired/turbo';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Turbo = Turbo;

// Turbo hanya untuk link yang opt-in (bottom-nav memakai data-turbo="true").
Turbo.session.drive = false;

document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        items: [],
        show(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            setTimeout(() => this.dismiss(id), 4000);
        },
        dismiss(id) {
            this.items = this.items.filter((item) => item.id !== id);
        },
    });

    Alpine.store('loading', {
        active: false,
        message: 'Memproses...',
        show(message = 'Memproses...') {
            this.message = message;
            this.active = true;
        },
        hide() {
            this.active = false;
        },
    });

    Alpine.data('confirmDialog', () => ({
        open: false,
        title: 'Konfirmasi',
        message: 'Apakah Anda yakin?',
        action: null,
        ask(title, message, action) {
            this.title = title;
            this.message = message;
            this.action = action;
            this.open = true;
        },
        confirm() {
            if (typeof this.action === 'function') {
                this.action();
            }
            this.open = false;
        },
        cancel() {
            this.open = false;
            this.action = null;
        },
    }));

    Alpine.data('uploadModal', () => ({
        open: false,
        menuOpen: false,
        title: '',
        action: '',
        slots: [0],
        nextSlotId: 1,
        maxSlots: 10,
        openFor(title, action) {
            this.menuOpen = false;
            this.title = title;
            this.action = action;
            this.slots = [0];
            this.nextSlotId = 1;
            this.open = true;
        },
        close() {
            this.open = false;
        },
        toggleMenu() {
            this.menuOpen = ! this.menuOpen;
        },
        closeMenu() {
            this.menuOpen = false;
        },
        addSlot() {
            if (this.slots.length >= this.maxSlots) {
                return;
            }

            this.slots.push(this.nextSlotId++);
        },
        removeSlot(id) {
            if (this.slots.length <= 1) {
                return;
            }

            this.slots = this.slots.filter((slot) => slot !== id);
        },
        prepareSubmit(event) {
            const form = event.target;

            form.querySelectorAll('input[type="file"]').forEach((input) => {
                if (!input.files || input.files.length === 0) {
                    input.removeAttribute('name');
                }
            });

            const hasFile = [...form.querySelectorAll('input[type="file"]')].some(
                (input) => input.files && input.files.length > 0
            );

            if (!hasFile) {
                event.preventDefault();
                Alpine.store('toast').show('Pilih minimal satu berkas.', 'error');
                return;
            }

            submitWithLoading(event, 'Mengunggah file...');
        },
    }));

    Alpine.data('fileSlots', (initialCount = 1) => ({
        slots: Array.from({ length: initialCount }, (_, index) => index),
        nextSlotId: initialCount,
        maxSlots: 10,
        addSlot() {
            if (this.slots.length >= this.maxSlots) {
                return;
            }

            this.slots.push(this.nextSlotId++);
        },
        removeSlot(id) {
            if (this.slots.length <= 1) {
                return;
            }

            this.slots = this.slots.filter((slot) => slot !== id);
        },
        stripEmptyFiles(root = this.$el) {
            root.querySelectorAll('input[type="file"]').forEach((input) => {
                if (!input.files || input.files.length === 0) {
                    input.removeAttribute('name');
                }
            });
        },
    }));

    Alpine.data('pwaInstall', () => ({
        deferredPrompt: null,
        canInstall: false,
        installed: window.matchMedia('(display-mode: standalone)').matches,
        init() {
            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                this.deferredPrompt = event;
                this.canInstall = true;
            });

            window.addEventListener('appinstalled', () => {
                this.canInstall = false;
                this.installed = true;
                this.deferredPrompt = null;
                Alpine.store('toast').show('SiKADO berhasil dipasang di perangkat Anda.');
            });
        },
        async install() {
            if (!this.deferredPrompt) {
                return;
            }

            this.deferredPrompt.prompt();
            await this.deferredPrompt.userChoice;
            this.deferredPrompt = null;
            this.canInstall = false;
        },
    }));
});

Alpine.start();

function consumePageFlash() {
    const el = document.getElementById('page-flash');

    if (! el) {
        return;
    }

    try {
        const data = JSON.parse(el.textContent || '{}');

        if (data.success) {
            Alpine.store('toast').show(data.success, 'success');
        }

        if (data.error) {
            Alpine.store('toast').show(data.error, 'error');
        }
    } catch {
        // Abaikan flash yang tidak valid.
    } finally {
        el.remove();
    }
}

document.addEventListener('turbo:before-render', () => {
    if (typeof Alpine.destroyTree === 'function') {
        Alpine.destroyTree(document.body);
    }
});

document.addEventListener('turbo:render', () => {
    if (typeof Alpine.initTree === 'function') {
        Alpine.initTree(document.body);
    }
});

document.addEventListener('turbo:load', () => {
    Alpine.store('loading').hide();
    consumePageFlash();
});

document.addEventListener('DOMContentLoaded', () => {
    consumePageFlash();
});

window.copyToClipboard = async function (text) {
    try {
        await navigator.clipboard.writeText(text);
        Alpine.store('toast').show('Tautan folder berhasil disalin.');
    } catch {
        Alpine.store('toast').show('Gagal menyalin tautan.', 'error');
    }
};

window.submitWithLoading = function (event, message = 'Memproses...') {
    Alpine.store('loading').show(message);
};

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Ignore SW registration errors in local development.
        });
    });
}
