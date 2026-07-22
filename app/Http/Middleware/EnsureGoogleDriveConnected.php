<?php

namespace App\Http\Middleware;

use App\Services\GoogleDriveService;
use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGoogleDriveConnected
{
    public function __construct(
        protected SettingService $settings,
        protected GoogleDriveService $drive
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->settings->isConnected()) {
            return $next($request);
        }

        if ($request->routeIs('settings.*')) {
            return $next($request);
        }

        if ($this->settings->isConfigured()) {
            $this->drive->resetClient();
            $result = $this->drive->testConnection();

            if ($result['success']) {
                return $next($request);
            }
        }

        if (! $this->settings->hasBuiltInCredentials()) {
            return redirect()
                ->route('settings.index')
                ->with(
                    'error',
                    'Kredensial Google Drive belum dikonfigurasi di aplikasi. Periksa file .env dan service-account.json.'
                );
        }

        return redirect()
            ->route('settings.index')
            ->with(
                'error',
                'Google Drive belum terhubung. Isi ID folder utama di Pengaturan, lalu uji koneksi.'
            );
    }
}
