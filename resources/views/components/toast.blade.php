<div
    class="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4"
    x-data
>
    <template x-for="item in $store.toast.items" :key="item.id">
        <div
            class="pointer-events-auto w-full max-w-sm rounded-2xl px-4 py-3 text-sm font-medium text-white shadow-lg"
            :class="item.type === 'error' ? 'bg-rose-600' : 'bg-emerald-600'"
            x-text="item.message"
            @click="$store.toast.dismiss(item.id)"
        ></div>
    </template>
</div>
