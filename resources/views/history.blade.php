@extends('layouts.app')

@section('title', 'Riwayat')
@section('heading', 'Riwayat')

@section('content')
    <x-filter-bar
        :action="route('history')"
        :categories="$categories"
        :statuses="$statuses"
        :filters="$filters"
    />

    <section class="space-y-2">
        <div class="flex items-center justify-between px-0.5">
            <h2 class="text-sm font-bold text-slate-900">Semua Kegiatan</h2>
            <span class="text-[11px] font-medium text-slate-500">{{ $activities->total() }} kegiatan</span>
        </div>

        @forelse ($activities as $activity)
            <x-activity-card :activity="$activity" :show-drive-actions="true" />
        @empty
            <x-empty-state
                title="Belum ada riwayat kegiatan"
                message="Tambahkan kegiatan pertama untuk mulai mencatat pelaporan BKD dan E-Kin."
            >
                <a href="{{ route('activities.create') }}" class="tap mt-3 inline-flex rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">
                    Tambah Kegiatan
                </a>
            </x-empty-state>
        @endforelse

        <div class="pt-1">
            {{ $activities->links() }}
        </div>
    </section>
@endsection

@section('fab')
    <a
        href="{{ route('activities.create') }}"
        class="fab tap flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg shadow-brand-600/30"
        aria-label="Tambah kegiatan"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
        </svg>
    </a>
@endsection
