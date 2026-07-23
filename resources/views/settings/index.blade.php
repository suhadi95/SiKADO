@extends('layouts.app')

@section('title', 'Pengaturan')
@section('heading', 'Pengaturan')

@section('content')
    @if (! app(\App\Services\SettingService::class)->isConnected())
        <div class="mb-3 rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-900">
            <p class="font-semibold">Google Drive belum terhubung</p>
            <p class="mt-0.5 leading-relaxed">
                @if (! ($drive['has_credentials'] ?? false))
                    Kredensial belum dikonfigurasi di aplikasi (.env / service-account.json).
                @else
                    Isi <strong>ID folder utama</strong>, lalu Simpan atau Uji Koneksi.
                @endif
            </p>
        </div>
    @endif

    <div class="space-y-3" x-data="{ editingCategory: null }">
        <section class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70">
            <div class="mb-2.5 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900">Google Drive</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500">Hanya ID folder utama yang diatur di sini.</p>
                </div>
                @php
                    $status = $drive['connection_status'] ?? 'belum_dikonfigurasi';
                    $statusClass = match ($status) {
                        'terhubung' => 'bg-emerald-50 text-emerald-700',
                        'gagal' => 'bg-rose-50 text-rose-700',
                        default => 'bg-amber-50 text-amber-700',
                    };
                    $statusLabel = match ($status) {
                        'terhubung' => 'Terhubung',
                        'gagal' => 'Gagal',
                        'belum_diuji' => 'Belum Diuji',
                        default => 'Belum Siap',
                    };
                @endphp
                <span class="shrink-0 rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="mb-2.5 rounded-lg bg-slate-50 px-2.5 py-2 text-xs text-slate-600">
                @if ($drive['has_credentials'])
                    <p class="font-medium text-emerald-700">Kredensial: terkonfigurasi di aplikasi</p>
                    @if ($drive['client_email'])
                        <p class="mt-0.5 truncate text-[11px] text-slate-500">{{ $drive['client_email'] }}</p>
                    @endif
                @else
                    <p class="font-medium text-amber-700">Kredensial belum dikonfigurasi</p>
                @endif
            </div>

            @if ($drive['connection_message'])
                <p class="mb-2.5 rounded-lg bg-slate-50 px-2.5 py-2 text-xs leading-relaxed text-slate-600">
                    {{ $drive['connection_message'] }}
                    @if ($drive['last_checked_at'])
                        <span class="mt-0.5 block text-[11px] text-slate-400">
                            Dicek: {{ $drive['last_checked_at']->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') }}
                        </span>
                    @endif
                </p>
            @endif

            <form
                method="POST"
                action="{{ route('settings.google-drive.update') }}"
                class="space-y-2.5"
                @submit="submitWithLoading($event, 'Menyimpan pengaturan...')"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-0.5 block text-xs font-semibold text-slate-700">ID Folder Utama</label>
                    <input
                        type="text"
                        name="root_folder_id"
                        value="{{ old('root_folder_id', $drive['root_folder_id']) }}"
                        required
                        placeholder="1AbCDefGhIjkLmNoPqRsTuVwXyZ"
                        class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                    @error('root_folder_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white">
                    Simpan ID Folder
                </button>
            </form>

            <div class="mt-2 grid grid-cols-2 gap-2">
                <form method="POST" action="{{ route('settings.google-drive.test') }}" @submit="submitWithLoading($event, 'Menguji koneksi...')">
                    @csrf
                    <button type="submit" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                        Uji Koneksi
                    </button>
                </form>
                @if ($drive['root_folder_url'])
                    <a
                        href="{{ $drive['root_folder_url'] }}"
                        target="_blank"
                        rel="noopener"
                        class="tap rounded-lg bg-slate-100 px-3 py-2 text-center text-sm font-semibold text-slate-700"
                    >
                        Buka Folder
                    </a>
                @else
                    <button type="button" disabled class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-400">
                        Buka Folder
                    </button>
                @endif
            </div>
        </section>

        <section class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70">
            <h2 class="text-sm font-bold text-slate-900">Kategori Kegiatan</h2>
            <p class="mt-0.5 text-[11px] text-slate-500">Klasifikasi kegiatan dosen.</p>

            <form
                method="POST"
                action="{{ route('settings.categories.store') }}"
                class="mt-2.5 space-y-2 rounded-lg bg-slate-50 p-2.5"
                @submit="submitWithLoading($event, 'Menambah kategori...')"
            >
                @csrf
                <div class="grid grid-cols-[1fr_auto] gap-2">
                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="Nama kategori baru"
                        class="rounded-lg border-slate-200 bg-white px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                    <input
                        type="color"
                        name="color"
                        value="#2563EB"
                        class="h-9 w-11 cursor-pointer rounded-lg border border-slate-200 bg-white p-1"
                    >
                </div>
                <label class="flex items-center gap-2 text-xs text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Aktifkan kategori
                </label>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">
                    Tambah Kategori
                </button>
            </form>

            <div class="mt-2.5 space-y-2">
                @forelse ($categories as $category)
                    <div class="rounded-lg border border-slate-200 p-2.5">
                        <div x-show="editingCategory !== {{ $category->id }}">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $category->color }}"></span>
                                    <h3 class="truncate text-sm font-semibold text-slate-900">{{ $category->name }}</h3>
                                </div>
                                <span class="shrink-0 text-[11px] {{ $category->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <button
                                    type="button"
                                    class="rounded-md bg-amber-50 px-2.5 py-1.5 text-[11px] font-semibold text-amber-700"
                                    @click="editingCategory = {{ $category->id }}"
                                >
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('settings.categories.toggle', $category) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md bg-slate-100 px-2.5 py-1.5 text-[11px] font-semibold text-slate-700">
                                        {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form
                                    id="delete-category-{{ $category->id }}"
                                    method="POST"
                                    action="{{ route('settings.categories.destroy', $category) }}"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        class="rounded-md bg-rose-50 px-2.5 py-1.5 text-[11px] font-semibold text-rose-700"
                                        @click="$dispatch('confirm-delete', {
                                            title: 'Hapus Kategori',
                                            message: 'Hapus kategori {{ $category->name }}?',
                                            formId: 'delete-category-{{ $category->id }}'
                                        })"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <form
                            x-show="editingCategory === {{ $category->id }}"
                            x-cloak
                            method="POST"
                            action="{{ route('settings.categories.update', $category) }}"
                            class="space-y-2"
                            @submit="submitWithLoading($event, 'Menyimpan kategori...')"
                        >
                            @csrf
                            @method('PUT')
                            <input
                                type="text"
                                name="name"
                                value="{{ $category->name }}"
                                required
                                class="w-full rounded-lg border-slate-200 bg-slate-50 px-2.5 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
                            >
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    name="color"
                                    value="{{ $category->color }}"
                                    class="h-9 w-11 cursor-pointer rounded-lg border border-slate-200 bg-white p-1"
                                >
                                <label class="flex items-center gap-2 text-xs text-slate-700">
                                    <input type="hidden" name="is_active" value="0">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked($category->is_active)
                                        class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    >
                                    Aktif
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold" @click="editingCategory = null">
                                    Batal
                                </button>
                                <button type="submit" class="flex-1 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                @empty
                    <x-empty-state title="Belum ada kategori" message="Tambahkan kategori untuk mengelompokkan kegiatan." />
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70">
            <h2 class="text-sm font-bold text-slate-900">Instalasi Aplikasi</h2>
            <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">
                Pasang sebagai PWA di smartphone. Upload Drive membutuhkan internet.
            </p>
            <div x-data="pwaInstall" class="mt-2">
                <template x-if="canInstall && !installed">
                    <button type="button" @click="install" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">
                        Pasang SiKADO
                    </button>
                </template>
                <template x-if="installed">
                    <p class="text-xs font-medium text-emerald-700">Sudah terpasang (standalone).</p>
                </template>
                <template x-if="!canInstall && !installed">
                    <p class="text-xs text-slate-500">Gunakan menu browser: Tambahkan ke Layar Utama.</p>
                </template>
            </div>
        </section>
    </div>
@endsection
