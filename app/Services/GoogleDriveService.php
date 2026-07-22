<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Exception as GoogleServiceException;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class GoogleDriveService
{
    protected ?Drive $drive = null;

    public function __construct(
        protected SettingService $settings
    ) {}

    public function isConfigured(): bool
    {
        return $this->settings->isConfigured();
    }

    public function getClient(): GoogleClient
    {
        if ($this->settings->hasOAuthCredentials()) {
            return $this->getOAuthClient();
        }

        return $this->getServiceAccountClient();
    }

    protected function getServiceAccountClient(): GoogleClient
    {
        $credentials = $this->settings->getCredentialsArray();

        if (! $credentials) {
            throw new RuntimeException('Kredensial Google Drive belum dikonfigurasi.');
        }

        $client = new GoogleClient;
        $client->setApplicationName(config('app.name', 'SiKADO'));
        $client->setScopes([Drive::DRIVE]);
        $client->setAuthConfig($credentials);
        $client->setAccessType('offline');

        return $client;
    }

    protected function getOAuthClient(): GoogleClient
    {
        $client = new GoogleClient;
        $client->setApplicationName(config('app.name', 'SiKADO'));
        $client->setClientId($this->settings->getOAuthClientId());
        $client->setClientSecret($this->settings->getOAuthClientSecret());
        $client->setAccessType('offline');
        $client->setScopes([Drive::DRIVE]);

        $token = $client->fetchAccessTokenWithRefreshToken(
            $this->settings->getOAuthRefreshToken()
        );

        if (isset($token['error'])) {
            $message = $token['error_description'] ?? $token['error'];
            throw new RuntimeException('OAuth Google Drive tidak valid: '.$message);
        }

        $client->setAccessToken($token);

        return $client;
    }

    public function getDrive(): Drive
    {
        if ($this->drive) {
            return $this->drive;
        }

        return $this->drive = new Drive($this->getClient());
    }

    public function resetClient(): void
    {
        $this->drive = null;
    }

    public function testConnection(): array
    {
        try {
            $this->resetClient();

            if (! $this->settings->isConfigured()) {
                throw new RuntimeException('Kredensial Google Drive (.env + service-account.json) dan ID folder utama belum lengkap.');
            }

            $rootFolderId = $this->settings->getRootFolderId();

            if (! $rootFolderId) {
                throw new RuntimeException('ID folder utama belum diisi.');
            }

            $file = $this->getDrive()->files->get($rootFolderId, [
                'fields' => 'id,name,mimeType,webViewLink',
                'supportsAllDrives' => true,
            ]);

            if ($file->getMimeType() !== 'application/vnd.google-apps.folder') {
                throw new RuntimeException('ID yang dimasukkan bukan folder Google Drive.');
            }

            $this->uploadProbeFile($rootFolderId);

            $authLabel = $this->settings->hasOAuthCredentials()
                ? 'OAuth akun Google Anda'
                : 'Service Account';

            $this->settings->markConnection(
                'terhubung',
                'Koneksi berhasil ke folder: '.$file->getName().' ('.$authLabel.'). Uji unggah file berhasil.'
            );

            return [
                'success' => true,
                'message' => 'Koneksi berhasil. Folder: '.$file->getName().'. Uji unggah file berhasil.',
                'folder' => [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'url' => $file->getWebViewLink() ?: 'https://drive.google.com/drive/folders/'.$file->getId(),
                ],
            ];
        } catch (Throwable $e) {
            $message = $this->friendlyError($e);
            $this->settings->markConnection('gagal', $message);

            return [
                'success' => false,
                'message' => $message,
            ];
        }
    }

    public function createFolder(string $name, ?string $parentId = null): array
    {
        $parentId ??= $this->settings->getRootFolderId();

        if (! $parentId) {
            throw new RuntimeException('ID folder utama belum dikonfigurasi.');
        }

        $metadata = new DriveFile([
            'name' => $this->sanitizeDriveName($name),
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);

        $folder = $this->getDrive()->files->create($metadata, [
            'fields' => 'id,name,webViewLink',
            'supportsAllDrives' => true,
        ]);

        return [
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'url' => $folder->getWebViewLink() ?: 'https://drive.google.com/drive/folders/'.$folder->getId(),
        ];
    }

    public function renameFolder(string $folderId, string $name): array
    {
        $metadata = new DriveFile([
            'name' => $this->sanitizeDriveName($name),
        ]);

        $folder = $this->getDrive()->files->update($folderId, $metadata, [
            'fields' => 'id,name,webViewLink',
            'supportsAllDrives' => true,
        ]);

        return [
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'url' => $folder->getWebViewLink() ?: 'https://drive.google.com/drive/folders/'.$folder->getId(),
        ];
    }

    public function uploadFile(string $folderId, UploadedFile $file, string $storedName): array
    {
        $storedName = $this->sanitizeDriveName($storedName);
        $path = $file->getRealPath() ?: $file->getPathname();
        $content = @file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('Gagal membaca file yang diunggah dari perangkat.');
        }

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $metadata = new DriveFile([
            'name' => $storedName,
            'parents' => [$folderId],
        ]);

        try {
            $uploaded = $this->getDrive()->files->create($metadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,mimeType,size,webViewLink',
                'supportsAllDrives' => true,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException($this->friendlyError($e), 0, $e);
        }

        return [
            'id' => $uploaded->getId(),
            'name' => $uploaded->getName(),
            'mime_type' => $uploaded->getMimeType(),
            'size' => (int) ($uploaded->getSize() ?: $file->getSize()),
            'url' => $uploaded->getWebViewLink(),
        ];
    }

    public function deleteFile(string $fileId): void
    {
        $this->getDrive()->files->delete($fileId, [
            'supportsAllDrives' => true,
        ]);
    }

    public function deleteFolder(string $folderId): void
    {
        $this->deleteFile($folderId);
    }

    public function getRootFolderUrl(): ?string
    {
        $folderId = $this->settings->getRootFolderId();

        return $folderId
            ? 'https://drive.google.com/drive/folders/'.$folderId
            : null;
    }

    protected function uploadProbeFile(string $folderId): void
    {
        $metadata = new DriveFile([
            'name' => 'SiKADO-probe-'.now()->format('YmdHis').'.txt',
            'parents' => [$folderId],
        ]);

        $uploaded = $this->getDrive()->files->create($metadata, [
            'data' => 'SiKADO connection test',
            'mimeType' => 'text/plain',
            'uploadType' => 'multipart',
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);

        $this->deleteFile($uploaded->getId());
    }

    protected function sanitizeDriveName(string $name): string
    {
        $name = preg_replace('/[\\\\\/]+/', ' ', $name) ?? $name;
        $name = preg_replace('/\s+/', ' ', trim($name)) ?? trim($name);
        $name = $name !== '' ? $name : 'file';

        if (mb_strlen($name) > 240) {
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $basename = mb_substr(pathinfo($name, PATHINFO_FILENAME), 0, 220);

            $name = $extension !== ''
                ? $basename.'.'.$extension
                : $basename;
        }

        return $name;
    }

    protected function friendlyError(Throwable $e): string
    {
        $message = $e->getMessage();

        if ($e instanceof GoogleServiceException) {
            $errors = $e->getErrors();
            if (! empty($errors[0]['message'])) {
                $message = $errors[0]['message'];
            }
        }

        if (str_contains($message, 'storageQuotaExceeded')
            || str_contains($message, 'Service Accounts do not have storage quota')) {
            return 'Upload gagal: akun Service Account tidak memiliki kuota penyimpanan. Untuk Google Drive pribadi (@gmail.com), konfigurasi OAuth di file .env aplikasi.';
        }

        if (str_contains($message, 'File not found') || str_contains($message, '404')) {
            return 'Folder tidak ditemukan. Pastikan ID folder benar dan telah dibagikan ke Service Account (atau OAuth sudah diisi).';
        }

        if (str_contains($message, 'invalid_grant') || str_contains($message, 'Invalid credentials')) {
            return 'Kredensial Google Drive tidak valid. Periksa JSON Service Account atau Refresh Token OAuth.';
        }

        if (str_contains($message, 'Insufficient Permission') || str_contains($message, '403')) {
            return 'Akses ditolak. Bagikan folder utama ke Service Account (Editor) atau gunakan OAuth akun Google Anda.';
        }

        return 'Gagal terhubung ke Google Drive: '.$message;
    }
}
