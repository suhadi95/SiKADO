@props([
    'activity',
    'showDriveActions' => false,
])

<article class="rounded-2xl bg-white p-2.5 shadow-sm ring-1 ring-slate-200/70" x-data="uploadModal">
    <div class="flex items-start gap-2">
        <div class="min-w-0 flex-1">
            <h3 class="text-[14px] font-semibold leading-snug text-slate-900">{{ $activity->name }}</h3>
            <p class="mt-0.5 text-[11px] leading-snug text-slate-600">
                <span class="font-medium text-slate-700">{{ $activity->activity_date->translatedFormat('d M Y') }}</span>
                <span class="text-slate-300"> · </span>
                <span style="color: {{ $activity->category?->color ?? '#64748B' }}">{{ $activity->category?->name ?? 'Tanpa Kategori' }}</span>
                @if ($showDriveActions)
                    <span class="text-slate-300"> · </span>
                    <span>{{ $activity->files_count }} file</span>
                @endif
            </p>
            <div class="mt-1">
                <span class="inline-flex rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $activity->status->colorClasses() }}">
                    {{ $activity->status_label }}
                </span>
            </div>
        </div>

        @if ($showDriveActions)
            {{-- Riwayat: aksi di balik menu titik tiga --}}
            <div class="relative shrink-0" @click.outside="closeMenu()">
                <button
                    type="button"
                    title="Menu aksi"
                    aria-label="Menu aksi"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700 active:bg-slate-200"
                    x-on:click="toggleMenu()"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="5" r="1.75" />
                        <circle cx="12" cy="12" r="1.75" />
                        <circle cx="12" cy="19" r="1.75" />
                    </svg>
                </button>

                <div
                    x-show="menuOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 top-10 z-40 w-52 origin-top-right overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-200"
                >
                    @if ($activity->drive_folder_url)
                        <a
                            href="{{ $activity->drive_folder_url }}"
                            target="_blank"
                            rel="noopener"
                            class="tap flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm text-slate-700 active:bg-slate-50"
                            x-on:click="closeMenu()"
                        >
                            <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                            </svg>
                            Buka folder Drive
                        </a>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm text-slate-700 active:bg-slate-50"
                            x-on:click="closeMenu(); copyToClipboard(@js($activity->drive_folder_url))"
                        >
                            <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Salin link folder
                        </button>
                    @endif

                    <button
                        type="button"
                        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm text-slate-700 active:bg-slate-50"
                        x-on:click="openFor('Upload Berkas Dasar', @js(route('activities.upload.basic', $activity)))"
                    >
                        <svg class="h-4 w-4 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Upload Berkas Dasar
                    </button>

                    <button
                        type="button"
                        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm text-slate-700 active:bg-slate-50"
                        x-on:click="openFor('Upload Bukti Kegiatan', @js(route('activities.upload.evidence', $activity)))"
                    >
                        <svg class="h-4 w-4 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Upload Bukti Kegiatan
                    </button>

                    <a
                        href="{{ route('activities.edit', $activity) }}"
                        class="tap flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm text-slate-700 active:bg-slate-50"
                        x-on:click="closeMenu()"
                    >
                        <svg class="h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit kegiatan
                    </a>

                    <div class="my-1 border-t border-slate-100"></div>

                    <form
                        id="delete-activity-{{ $activity->id }}"
                        method="POST"
                        action="{{ route('activities.destroy', $activity) }}"
                        x-on:submit="submitWithLoading($event, 'Menghapus kegiatan...')"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm font-medium text-rose-600 active:bg-rose-50"
                            x-on:click="closeMenu(); $dispatch('confirm-delete', {
                                title: 'Hapus Kegiatan',
                                message: @js('Kegiatan "'.$activity->name.'" akan dihapus beserta file di Google Drive. Lanjutkan?'),
                                formId: @js('delete-activity-'.$activity->id)
                            })"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus kegiatan
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- Dashboard: aksi tetap terlihat --}}
            <div class="grid shrink-0 grid-cols-2 gap-1">
                <button
                    type="button"
                    title="Upload berkas dasar"
                    aria-label="Upload berkas dasar"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700 active:bg-brand-100"
                    x-on:click="openFor('Upload Berkas Dasar', @js(route('activities.upload.basic', $activity)))"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </button>

                <button
                    type="button"
                    title="Upload bukti kegiatan"
                    aria-label="Upload bukti kegiatan"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700 active:bg-brand-100"
                    x-on:click="openFor('Upload Bukti Kegiatan', @js(route('activities.upload.evidence', $activity)))"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </button>

                <a
                    href="{{ route('activities.edit', $activity) }}"
                    title="Edit kegiatan"
                    aria-label="Edit kegiatan"
                    class="tap inline-flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700 active:bg-amber-100"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </a>

                <form
                    id="delete-activity-{{ $activity->id }}"
                    method="POST"
                    action="{{ route('activities.destroy', $activity) }}"
                    class="inline"
                    x-on:submit="submitWithLoading($event, 'Menghapus kegiatan...')"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        title="Hapus kegiatan"
                        aria-label="Hapus kegiatan"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-700 active:bg-rose-100"
                        x-on:click="$dispatch('confirm-delete', {
                            title: 'Hapus Kegiatan',
                            message: @js('Kegiatan "'.$activity->name.'" akan dihapus beserta file di Google Drive. Lanjutkan?'),
                            formId: @js('delete-activity-'.$activity->id)
                        })"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[65] flex items-end justify-center bg-slate-900/40 p-3 sm:items-center"
    >
        <div class="absolute inset-0" x-on:click="close"></div>
        <div class="relative max-h-[85dvh] w-full max-w-sm overflow-y-auto rounded-2xl bg-white p-4 shadow-2xl">
            <h3 class="text-sm font-bold text-slate-900" x-text="title"></h3>
            <p class="mt-1 text-xs text-slate-500">Pilih satu berkas. Tambah lagi jika perlu (maks. 50 MB/file).</p>
            <form
                method="POST"
                :action="action"
                enctype="multipart/form-data"
                class="mt-3 space-y-3"
                x-on:submit="prepareSubmit($event)"
            >
                @csrf

                <div class="space-y-2">
                    <template x-for="(slot, index) in slots" :key="slot">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <label class="text-[11px] font-semibold text-slate-600" x-text="'Berkas ' + (index + 1)"></label>
                                <button
                                    type="button"
                                    class="text-[11px] font-semibold text-rose-600 disabled:invisible"
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
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-dashed border-brand-300 bg-brand-50/60 px-3 py-2 text-sm font-semibold text-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
                    x-on:click="addSlot"
                    :disabled="slots.length >= maxSlots"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                    </svg>
                    Tambah berkas
                </button>

                <div class="flex gap-2">
                    <button type="button" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold" x-on:click="close">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">
                        Unggah
                    </button>
                </div>
            </form>
        </div>
    </div>
</article>
