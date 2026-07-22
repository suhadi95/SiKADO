@extends('layouts.app')

@section('title', 'Edit Kegiatan')
@section('heading', 'Edit Kegiatan')

@section('content')
    <form
        method="POST"
        action="{{ route('activities.update', $activity) }}"
        class="space-y-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70"
        @submit="submitWithLoading($event, 'Menyimpan perubahan...')"
    >
        @csrf
        @method('PUT')

        <div>
            <label class="mb-0.5 block text-xs font-semibold text-slate-700">Tanggal Kegiatan</label>
            <input
                type="date"
                name="activity_date"
                value="{{ old('activity_date', $activity->activity_date->toDateString()) }}"
                required
                class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
            >
            @error('activity_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-0.5 block text-xs font-semibold text-slate-700">Nama Kegiatan</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $activity->name) }}"
                required
                maxlength="255"
                class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
            >
            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-0.5 block text-xs font-semibold text-slate-700">Kategori Kegiatan</label>
            <select
                name="category_id"
                required
                class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
            >
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $activity->category_id) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-lg bg-slate-50 px-2.5 py-2">
            <label class="flex items-start gap-2">
                <input type="hidden" name="requires_evidence" value="0">
                <input
                    type="checkbox"
                    name="requires_evidence"
                    value="1"
                    class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    @checked(old('requires_evidence', $activity->requires_evidence))
                >
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Membutuhkan bukti</span>
                    <span class="block text-[11px] text-slate-500">Ubah tanggal/nama akan menyesuaikan nama folder Drive.</span>
                </span>
            </label>
        </div>

        <div>
            <label class="mb-0.5 block text-xs font-semibold text-slate-700">Catatan (opsional)</label>
            <textarea
                name="notes"
                rows="2"
                class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
            >{{ old('notes', $activity->notes) }}</textarea>
            @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        @if ($activity->drive_folder_url)
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-sm">
                <p class="text-xs font-semibold text-slate-800">Folder Google Drive</p>
                <p class="mt-0.5 truncate text-xs text-slate-600">{{ $activity->drive_folder_name }}</p>
                <a href="{{ $activity->drive_folder_url }}" target="_blank" rel="noopener" class="mt-1 inline-flex text-xs font-semibold text-brand-700">
                    Buka folder
                </a>
            </div>
        @endif

        <div class="flex gap-2 pt-1">
            <a href="{{ url()->previous() }}" class="flex-1 rounded-lg border border-slate-200 px-3 py-2.5 text-center text-sm font-semibold text-slate-700">
                Batal
            </a>
            <button type="submit" class="flex-1 rounded-lg bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white">
                Simpan
            </button>
        </div>
    </form>
@endsection
