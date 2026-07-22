<div
    x-data
    x-show="$store.loading.active"
    x-cloak
    class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
>
    <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 shadow-xl">
        <span class="h-5 w-5 animate-spin rounded-full border-2 border-brand-600 border-t-transparent"></span>
        <span class="text-sm font-medium text-slate-700" x-text="$store.loading.message"></span>
    </div>
</div>
