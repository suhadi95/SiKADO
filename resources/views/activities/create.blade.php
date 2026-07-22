@extends('layouts.app')

@section('title', 'Tambah Kegiatan')
@section('heading', 'Tambah Kegiatan')

@section('content')
    <form
        method="POST"
        action="{{ route('activities.store') }}"
        enctype="multipart/form-data"
        class="space-y-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70"
        x-data
        @submit="
            $el.querySelectorAll('input[type=file]').forEach((input) => {
                if (!input.files || input.files.length === 0) input.removeAttribute('name');
            });
            submitWithLoading($event, 'Menyimpan kegiatan...');
        "
    >
        @csrf

        <div>
            <label class="mb-0.5 block text-xs font-semibold text-slate-700">Tanggal Kegiatan</label>
            <input
                type="date"
                name="activity_date"
                value="{{ old('activity_date', now()->toDateString()) }}"
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
                value="{{ old('name') }}"
                required
                maxlength="255"
                placeholder="Contoh: Seminar Nasional Pendidikan"
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
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-lg bg-slate-50 px-2.5 py-2">
            <input type="hidden" name="requires_evidence" value="0">
            <label class="flex items-start gap-2">
                <input
                    type="checkbox"
                    name="requires_evidence"
                    value="1"
                    class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    @checked(old('requires_evidence', true))
                >
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Membutuhkan bukti</span>
                    <span class="block text-[11px] text-slate-500">Jika tidak dicentang, status menjadi Tidak Memerlukan Bukti.</span>
                </span>
            </label>
        </div>

        <div>
            <label class="mb-0.5 block text-xs font-semibold text-slate-700">Catatan (opsional)</label>
            <textarea
                name="notes"
                rows="2"
                class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
                placeholder="Catatan tambahan"
            >{{ old('notes') }}</textarea>
            @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div x-data="fileSlots()">
            <label class="mb-0.5 block text-xs font-semibold text-slate-700">Berkas Dasar (opsional)</label>
            <div class="mt-1 space-y-2">
                <template x-for="(slot, index) in slots" :key="slot">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                        <div class="mb-1.5 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-semibold text-slate-600" x-text="'Berkas ' + (index + 1)"></span>
                            <button
                                type="button"
                                class="text-[11px] font-semibold text-rose-600"
                                x-show="slots.length > 1"
                                x-on:click="removeSlot(slot)"
                            >
                                Hapus
                            </button>
                        </div>
                        <input
                            type="file"
                            name="files[]"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                            class="block w-full text-xs text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-brand-50 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700"
                        >
                    </div>
                </template>
            </div>
            <button
                type="button"
                class="mt-2 inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-brand-300 bg-brand-50/60 px-3 py-2 text-sm font-semibold text-brand-700 disabled:opacity-50"
                x-on:click="addSlot"
                :disabled="slots.length >= maxSlots"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                </svg>
                Tambah berkas
            </button>
            <p class="mt-1 text-[11px] text-slate-500">Opsional. Dasar kegiatan (undangan, surat tugas, dll). Maks. 10 MB/file.</p>
            @error('files') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            @error('files.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-2 pt-1">
            <a href="{{ route('dashboard') }}" class="flex-1 rounded-lg border border-slate-200 px-3 py-2.5 text-center text-sm font-semibold text-slate-700">
                Batal
            </a>
            <button type="submit" class="flex-1 rounded-lg bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white">
                Simpan
            </button>
        </div>
    </form>
@endsection
