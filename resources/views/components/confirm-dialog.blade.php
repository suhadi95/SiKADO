<div
    x-data="confirmDialog"
    x-cloak
    @confirm-delete.window="
        ask($event.detail.title, $event.detail.message, () => {
            const form = document.getElementById($event.detail.formId);
            if (form) {
                Alpine.store('loading').show('Menghapus...');
                form.submit();
            }
        })
    "
>
    <div
        x-show="open"
        class="fixed inset-0 z-[70] flex items-end justify-center bg-slate-900/40 p-4 sm:items-center"
        @keydown.escape.window="cancel"
    >
        <div class="absolute inset-0" @click="cancel"></div>
        <div class="relative w-full max-w-sm rounded-3xl bg-white p-5 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900" x-text="title"></h3>
            <p class="mt-2 text-sm text-slate-600" x-text="message"></p>
            <div class="mt-5 flex gap-2">
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700"
                    @click="cancel"
                >
                    Batal
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white"
                    @click="confirm"
                >
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>
