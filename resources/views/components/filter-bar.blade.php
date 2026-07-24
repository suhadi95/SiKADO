@props([
    'action' => '',
    'categories' => collect(),
    'statuses' => [],
    'filters' => [],
])

@php
    $sort = $filters['sort'] ?? 'date_desc';
    $hasActiveFilter = filled($filters['name'] ?? null)
        || filled($filters['category_id'] ?? null)
        || filled($filters['status'] ?? null)
        || filled($filters['month'] ?? null)
        || filled($filters['year'] ?? null)
        || $sort === 'date_asc';
    $sortLabel = $sort === 'date_asc' ? 'Tanggal terlama' : 'Tanggal terbaru';
@endphp

<div
    class="mb-3 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70"
    x-data="{ open: {{ $hasActiveFilter ? 'true' : 'false' }} }"
>
    <button
        type="button"
        class="flex w-full items-center justify-between gap-2 px-3 py-2.5 text-left"
        @click="open = !open"
    >
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900">Filter & Urutan</p>
            <p class="truncate text-[11px] text-slate-500">
                @if ($hasActiveFilter)
                    {{ $sortLabel }} · filter aktif — ketuk untuk ubah
                @else
                    {{ $sortLabel }} · cari nama, kategori, status, periode
                @endif
            </p>
        </div>
        <span class="shrink-0 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600" x-text="open ? 'Tutup' : 'Buka'"></span>
    </button>

    <form
        method="GET"
        action="{{ $action }}"
        class="space-y-2.5 border-t border-slate-100 px-3 pb-3 pt-2.5"
        x-show="open"
        x-cloak
    >
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-500">Urutkan tanggal</label>
            <select name="sort" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="date_desc" @selected($sort === 'date_desc')>Tanggal terbaru</option>
                <option value="date_asc" @selected($sort === 'date_asc')>Tanggal terlama</option>
            </select>
        </div>

        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-500">Nama kegiatan</label>
            <input
                type="search"
                name="name"
                value="{{ $filters['name'] ?? '' }}"
                placeholder="Cari nama..."
                class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
            >
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="mb-0.5 block text-[11px] font-semibold text-slate-500">Kategori</label>
                <select name="category_id" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Semua</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-0.5 block text-[11px] font-semibold text-slate-500">Status</label>
                <select name="status" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Semua</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-0.5 block text-[11px] font-semibold text-slate-500">Bulan</label>
                <select name="month" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Semua</option>
                    @foreach (range(1, 12) as $month)
                        <option value="{{ $month }}" @selected(($filters['month'] ?? null) == $month)>
                            {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-0.5 block text-[11px] font-semibold text-slate-500">Tahun</label>
                <select name="year" class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Semua</option>
                    @foreach (range(now()->year, now()->year - 5) as $year)
                        <option value="{{ $year }}" @selected(($filters['year'] ?? null) == $year)>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">
                Terapkan
            </button>
            <a href="{{ $action }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600">
                Reset
            </a>
        </div>
    </form>
</div>
