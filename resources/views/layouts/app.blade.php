<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SiKADO">
    <meta name="description" content="Sistem Informasi Kegiatan Dosen untuk pelaporan BKD dan E-Kin.">

    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet">

    <title>@yield('title', 'SiKADO') — Sistem Informasi Kegiatan Dosen</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-800 antialiased" x-data>
    <div class="app-shell mx-auto min-h-screen max-w-3xl">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
            <div class="flex items-center justify-between px-3 py-2">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-brand-600">SiKADO</p>
                    <h1 class="truncate text-base font-bold leading-tight text-slate-900">@yield('heading', 'Dashboard')</h1>
                </div>
                <div x-data="pwaInstall" class="flex shrink-0 items-center gap-2">
                    <template x-if="canInstall && !installed">
                        <button
                            type="button"
                            @click="install"
                            class="rounded-lg bg-brand-600 px-2.5 py-1 text-[11px] font-semibold text-white"
                        >
                            Pasang
                        </button>
                    </template>
                </div>
            </div>
        </header>

        <main class="px-3 py-3">
            @yield('content')
        </main>
    </div>

    @include('components.bottom-nav')
    @yield('fab')
    @include('components.toast')
    @include('components.loading')
    @include('components.confirm-dialog')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if (session('success'))
                Alpine.store('toast').show(@json(session('success')), 'success');
            @endif
            @if (session('error'))
                Alpine.store('toast').show(@json(session('error')), 'error');
            @endif
            @if ($errors->any())
                Alpine.store('toast').show(@json($errors->first()), 'error');
            @endif
        });
    </script>
</body>
</html>
