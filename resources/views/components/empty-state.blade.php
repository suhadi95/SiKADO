@props(['title' => 'Belum ada data', 'message' => 'Data yang Anda cari belum tersedia.'])

<div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 px-4 py-8 text-center">
    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
        </svg>
    </div>
    <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
    <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $message }}</p>
    {{ $slot }}
</div>
