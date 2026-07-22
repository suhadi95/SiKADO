<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Carbon;

class SettingService
{
    public const KEY_ROOT_FOLDER_ID = 'google_drive_root_folder_id';

    public const KEY_CONNECTION_STATUS = 'google_drive_connection_status';

    public const KEY_LAST_CHECKED_AT = 'google_drive_last_checked_at';

    public const KEY_CONNECTION_MESSAGE = 'google_drive_connection_message';

    public function getServiceAccountPath(): string
    {
        $path = config('google-drive.service_account_path');

        if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return base_path($path);
        }

        return $path;
    }

    public function getCredentialsJson(): ?string
    {
        $path = $this->getServiceAccountPath();

        if (! is_readable($path)) {
            return null;
        }

        $json = file_get_contents($path);

        return $json !== false ? $json : null;
    }

    public function getCredentialsArray(): ?array
    {
        $json = $this->getCredentialsJson();

        if (! $json) {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function hasServiceAccount(): bool
    {
        $credentials = $this->getCredentialsArray();

        return is_array($credentials)
            && filled($credentials['client_email'] ?? null)
            && filled($credentials['private_key'] ?? null);
    }

    public function getRootFolderId(): ?string
    {
        $value = Setting::getValue(self::KEY_ROOT_FOLDER_ID);

        return filled($value) ? (string) $value : null;
    }

    public function setRootFolderId(?string $folderId): void
    {
        Setting::setValue(self::KEY_ROOT_FOLDER_ID, $folderId);
    }

    public function getOAuthClientId(): ?string
    {
        $value = config('google-drive.oauth_client_id');

        return filled($value) ? (string) $value : null;
    }

    public function getOAuthClientSecret(): ?string
    {
        $value = config('google-drive.oauth_client_secret');

        return filled($value) ? (string) $value : null;
    }

    public function getOAuthRefreshToken(): ?string
    {
        $value = config('google-drive.oauth_refresh_token');

        return filled($value) ? (string) $value : null;
    }

    public function hasOAuthCredentials(): bool
    {
        return filled($this->getOAuthClientId())
            && filled($this->getOAuthClientSecret())
            && filled($this->getOAuthRefreshToken());
    }

    public function hasBuiltInCredentials(): bool
    {
        return $this->hasServiceAccount() && $this->hasOAuthCredentials();
    }

    public function getConnectionStatus(): string
    {
        return (string) (Setting::getValue(self::KEY_CONNECTION_STATUS) ?? 'belum_dikonfigurasi');
    }

    public function getConnectionMessage(): ?string
    {
        return Setting::getValue(self::KEY_CONNECTION_MESSAGE);
    }

    public function getLastCheckedAt(): ?Carbon
    {
        $value = Setting::getValue(self::KEY_LAST_CHECKED_AT);

        return $value ? Carbon::parse($value) : null;
    }

    public function markConnection(string $status, ?string $message = null): void
    {
        Setting::setValue(self::KEY_CONNECTION_STATUS, $status);
        Setting::setValue(self::KEY_CONNECTION_MESSAGE, $message);
        Setting::setValue(self::KEY_LAST_CHECKED_AT, now()->toIso8601String());
    }

    public function isConfigured(): bool
    {
        return $this->hasBuiltInCredentials() && filled($this->getRootFolderId());
    }

    public function isConnected(): bool
    {
        return $this->getConnectionStatus() === 'terhubung';
    }

    public function usesOAuth(): bool
    {
        return $this->hasOAuthCredentials();
    }

    public function getDriveSettings(): array
    {
        $credentials = $this->getCredentialsArray();

        return [
            'has_credentials' => $this->hasBuiltInCredentials(),
            'client_email' => $credentials['client_email'] ?? null,
            'has_oauth' => $this->hasOAuthCredentials(),
            'auth_mode' => $this->hasOAuthCredentials() ? 'oauth' : 'service_account',
            'service_account_path' => $this->getServiceAccountPath(),
            'root_folder_id' => $this->getRootFolderId(),
            'connection_status' => $this->getConnectionStatus(),
            'connection_message' => $this->getConnectionMessage(),
            'last_checked_at' => $this->getLastCheckedAt(),
            'root_folder_url' => $this->getRootFolderId()
                ? 'https://drive.google.com/drive/folders/'.$this->getRootFolderId()
                : null,
        ];
    }
}
