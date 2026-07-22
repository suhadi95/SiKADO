@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <section class="mb-3 grid grid-cols-4 gap-1.5">
        <div class="rounded-xl bg-white px-2 py-2 text-center shadow-sm ring-1 ring-slate-200/70">
            <p class="text-[10px] font-semibold leading-tight text-slate-500">Total</p>
            <p class="mt-0.5 text-lg font-bold leading-none text-slate-900">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-xl bg-white px-2 py-2 text-center shadow-sm ring-1 ring-slate-200/70">
            <p class="text-[10px] font-semibold leading-tight text-slate-500">Bukti</p>
            <p class="mt-0.5 text-lg font-bold leading-none text-amber-600">{{ $summary['needs_evidence'] }}</p>
        </div>
        <div class="rounded-xl bg-white px-2 py-2 text-center shadow-sm ring-1 ring-slate-200/70">
            <p class="text-[10px] font-semibold leading-tight text-slate-500">Lengkap</p>
            <p class="mt-0.5 text-lg font-bold leading-none text-emerald-600">{{ $summary['complete'] }}</p>
        </div>
        <div class="rounded-xl bg-white px-2 py-2 text-center shadow-sm ring-1 ring-slate-200/70">
            <p class="text-[10px] font-semibold leading-tight text-slate-500">Kategori</p>
            <p class="mt-0.5 text-lg font-bold leading-none text-brand-700">{{ $summary['by_category']->where('is_active', true)->count() }}</p>
        </div>
    </section>

    @if ($summary['by_category']->isNotEmpty())
        <section class="mb-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70">
            <h2 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Per kategori</h2>
            <div class="space-y-1.5">
                @foreach ($summary['by_category'] as $category)
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-1.5">
                            <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $category->color }}"></span>
                            <span class="truncate text-sm text-slate-700">{{ $category->name }}</span>
                        </div>
                        <span class="text-sm font-semibold tabular-nums text-slate-900">{{ $category->activities_count }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <x-filter-bar
        :action="route('dashboard')"
        :categories="$categories"
        :statuses="$statuses"
        :filters="$filters"
    />

    <section class="space-y-2">
        <div class="flex items-center justify-between px-0.5">
            <h2 class="text-sm font-bold text-slate-900">Butuh Bukti</h2>
            <span class="text-[11px] font-medium text-slate-500">{{ $activities->total() }} kegiatan</span>
        </div>

        @forelse ($activities as $activity)
            <x-activity-card :activity="$activity" />
        @empty
            <x-empty-state
                title="Tidak ada kegiatan yang butuh bukti"
                message="Semua kegiatan sudah lengkap, atau belum ada data yang cocok dengan filter."
            />
        @endforelse

        <div class="pt-1">
            {{ $activities->links() }}
        </div>
    </section>
@endsection

@section('fab')
    <a
        href="{{ route('activities.create') }}"
        class="fab flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg shadow-brand-600/30"
        aria-label="Tambah kegiatan"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
        </svg>
    </a>
@endsection
