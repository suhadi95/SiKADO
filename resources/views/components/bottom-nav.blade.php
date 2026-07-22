<nav class="bottom-nav border-t border-slate-200 bg-white/95 shadow-[0_-2px_12px_rgba(15,23,42,0.05)] backdrop-blur">
    <div class="mx-auto grid max-w-3xl grid-cols-3">
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center gap-0.5 px-1 py-2 text-[11px] font-medium {{ request()->routeIs('dashboard') ? 'text-brand-700' : 'text-slate-500' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8M5 10v10h14V10" />
            </svg>
            Dashboard
        </a>
        <a href="{{ route('history') }}"
           class="flex flex-col items-center gap-0.5 px-1 py-2 text-[11px] font-medium {{ request()->routeIs('history') ? 'text-brand-700' : 'text-slate-500' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Riwayat
        </a>
        <a href="{{ route('settings.index') }}"
           class="flex flex-col items-center gap-0.5 px-1 py-2 text-[11px] font-medium {{ request()->routeIs('settings.*') ? 'text-brand-700' : 'text-slate-500' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.724 1.724 0 013.35 0l.286.956a1.724 1.724 0 001.58 1.158l1.01-.05a1.724 1.724 0 011.68 2.163l-.38.94a1.724 1.724 0 00.63 1.9l.79.64a1.724 1.724 0 01-.63 3.02l-.95.3a1.724 1.724 0 00-1.158 1.58l.05 1.01a1.724 1.724 0 01-2.163 1.68l-.94-.38a1.724 1.724 0 00-1.9.63l-.64.79a1.724 1.724 0 01-3.02-.63l-.3-.95a1.724 1.724 0 00-1.58-1.158l-1.01.05a1.724 1.724 0 01-1.68-2.163l.38-.94a1.724 1.724 0 00-.63-1.9l-.79-.64a1.724 1.724 0 01.63-3.02l.95-.3a1.724 1.724 0 001.158-1.58l-.05-1.01a1.724 1.724 0 012.163-1.68l.94.38c.63.25 1.34.1 1.9-.38l.64-.79z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Pengaturan
        </a>
    </div>
</nav>
