<?php

namespace App\Services;

use App\Models\DatabaseBackupSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveBackupClient
{
    private const AUTH_URI = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URI = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URI = 'https://www.googleapis.com/oauth2/v2/userinfo';
    private const DRIVE_FILES_URI = 'https://www.googleapis.com/drive/v3/files';
    private const DRIVE_UPLOAD_URI = 'https://www.googleapis.com/upload/drive/v3/files';
    private const DRIVE_ABOUT_URI = 'https://www.googleapis.com/drive/v3/about';

    public function buildAuthorizationUrl(string $clientId, string $redirectUri, string $state): string
    {
        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/userinfo.email',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return self::AUTH_URI.'?'.$query;
    }

    /**
     * @return array{access_token: string, refresh_token: ?string, email: ?string}
     */
    public function exchangeAuthorizationCode(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $code
    ): array {
        $response = Http::asForm()
            ->timeout(30)
            ->post(self::TOKEN_URI, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Google OAuth token exchange failed: '.$this->extractErrorMessage($response));
        }

        $data = $response->json();
        $accessToken = (string) ($data['access_token'] ?? '');
        if ($accessToken === '') {
            throw new RuntimeException('Google OAuth response did not include an access token.');
        }

        $email = $this->fetchAuthenticatedEmail($accessToken);

        return [
            'access_token' => $accessToken,
            'refresh_token' => !empty($data['refresh_token']) ? (string) $data['refresh_token'] : null,
            'email' => $email,
        ];
    }

    public function getAccessToken(DatabaseBackupSetting $setting): string
    {
        if ($setting->auth_mode === 'service_account') {
            return $this->getServiceAccountAccessToken($setting);
        }

        return $this->getOAuthAccessToken($setting);
    }

    /**
     * @return array{email: ?string, folder_id: ?string, folder_name: ?string, storage_quota: ?array}
     */
    public function verifyConnection(DatabaseBackupSetting $setting): array
    {
        $accessToken = $this->getAccessToken($setting);

        $aboutResp = Http::withToken($accessToken)
            ->timeout(20)
            ->get(self::DRIVE_ABOUT_URI, [
                'fields' => 'user(emailAddress,displayName),storageQuota',
            ]);

        if (!$aboutResp->successful()) {
            throw new RuntimeException('Google Drive verification failed: '.$this->extractErrorMessage($aboutResp));
        }

        $about = $aboutResp->json();
        $email = $about['user']['emailAddress'] ?? null;

        $folderId = $setting->effectiveFolderId();
        $folderName = null;
        if ($folderId) {
            $folderResp = Http::withToken($accessToken)
                ->timeout(20)
                ->get(self::DRIVE_FILES_URI.'/'.urlencode($folderId), [
                    'fields' => 'id,name,mimeType,trashed',
                    'supportsAllDrives' => 'true',
                ]);

            if (!$folderResp->successful()) {
                throw new RuntimeException("Configured Google Drive Folder ID '{$folderId}' is inaccessible: ".$this->extractErrorMessage($folderResp));
            }

            $folderMeta = $folderResp->json();
            if (($folderMeta['mimeType'] ?? '') !== 'application/vnd.google-apps.folder') {
                throw new RuntimeException("Google Drive ID '{$folderId}' is not a folder.");
            }
            if (!empty($folderMeta['trashed'])) {
                throw new RuntimeException("Configured Google Drive folder '{$folderMeta['name']}' is in Trash.");
            }

            $folderName = $folderMeta['name'] ?? null;
        }

        return [
            'email' => $email,
            'folder_id' => $folderId,
            'folder_name' => $folderName,
            'storage_quota' => $about['storageQuota'] ?? null,
        ];
    }

    public function ensureTargetFolder(string $accessToken, ?string $parentFolderId, ?string $subfolderName): ?string
    {
        $sub = trim((string) $subfolderName);
        $parent = trim((string) $parentFolderId);

        if ($sub === '') {
            return $parent !== '' ? $parent : null;
        }

        $escapedName = str_replace(['\\', "'"], ['\\\\', "\\'"], $sub);
        $parentClause = $parent !== '' ? "'{$parent}' in parents" : "'root' in parents";
        $query = "mimeType = 'application/vnd.google-apps.folder' and trashed = false and name = '{$escapedName}' and {$parentClause}";

        $searchResp = Http::withToken($accessToken)
            ->timeout(20)
            ->get(self::DRIVE_FILES_URI, [
                'q' => $query,
                'fields' => 'files(id,name)',
                'spaces' => 'drive',
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
                'pageSize' => 1,
            ]);

        if ($searchResp->successful()) {
            $existingId = $searchResp->json('files.0.id');
            if (is_string($existingId) && $existingId !== '') {
                return $existingId;
            }
        }

        $metadata = [
            'name' => $sub,
            'mimeType' => 'application/vnd.google-apps.folder',
        ];
        if ($parent !== '') {
            $metadata['parents'] = [$parent];
        }

        $createResp = Http::withToken($accessToken)
            ->timeout(20)
            ->post(self::DRIVE_FILES_URI.'?supportsAllDrives=true&fields=id,name', $metadata);

        if (!$createResp->successful()) {
            throw new RuntimeException("Failed to create Google Drive backup folder '{$sub}': ".$this->extractErrorMessage($createResp));
        }

        return (string) $createResp->json('id');
    }

    /**
     * @return array{id: string, name: string, webViewLink: ?string, size: ?string}
     */
    public function uploadFile(
        string $accessToken,
        string $localFilePath,
        string $fileName,
        ?string $folderId,
        string $mimeType = 'application/gzip'
    ): array {
        if (!is_file($localFilePath) || !is_readable($localFilePath)) {
            throw new RuntimeException("Backup archive not readable at {$localFilePath}.");
        }

        $totalSize = filesize($localFilePath);
        if ($totalSize === false || $totalSize <= 0) {
            throw new RuntimeException("Backup archive '{$fileName}' is empty.");
        }

        $metadata = ['name' => $fileName];
        if ($folderId) {
            $metadata['parents'] = [$folderId];
        }

        $initResp = Http::withToken($accessToken)
            ->withHeaders([
                'X-Upload-Content-Type' => $mimeType,
                'X-Upload-Content-Length' => (string) $totalSize,
            ])
            ->timeout(30)
            ->post(
                self::DRIVE_UPLOAD_URI.'?uploadType=resumable&supportsAllDrives=true&fields=id,name,webViewLink,size',
                $metadata
            );

        if (!$initResp->successful()) {
            throw new RuntimeException('Failed to start Google Drive upload: '.$this->extractErrorMessage($initResp));
        }

        $uploadUrl = $initResp->header('Location');
        if (!$uploadUrl) {
            throw new RuntimeException('Google Drive resumable upload response did not return an upload Location URL.');
        }

        $chunkSize = max(256 * 1024, (int) config('backup.google_drive.upload_chunk_bytes', 8 * 1024 * 1024));
        // Google requires chunk sizes (except last chunk) to be multiples of 256 KiB.
        $chunkSize = (int) (floor($chunkSize / (256 * 1024)) * 256 * 1024);

        $handle = fopen($localFilePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Unable to open backup file {$localFilePath} for upload.");
        }

        try {
            $offset = 0;
            $lastResponse = null;

            while ($offset < $totalSize) {
                $chunk = fread($handle, $chunkSize);
                if ($chunk === false || $chunk === '') {
                    break;
                }

                $chunkLen = strlen($chunk);
                $end = $offset + $chunkLen - 1;

                $lastResponse = Http::withHeaders([
                    'Content-Length' => (string) $chunkLen,
                    'Content-Range' => "bytes {$offset}-{$end}/{$totalSize}",
                ])
                    ->timeout(120)
                    ->withBody($chunk, $mimeType)
                    ->put($uploadUrl);

                $status = $lastResponse->status();
                if ($status === 308) {
                    $offset += $chunkLen;
                    continue;
                }

                if ($lastResponse->successful()) {
                    $uploaded = $lastResponse->json();
                    return [
                        'id' => (string) ($uploaded['id'] ?? ''),
                        'name' => (string) ($uploaded['name'] ?? $fileName),
                        'webViewLink' => $uploaded['webViewLink'] ?? null,
                        'size' => $uploaded['size'] ?? null,
                    ];
                }

                throw new RuntimeException('Google Drive upload failed: '.$this->extractErrorMessage($lastResponse));
            }

            throw new RuntimeException('Google Drive upload finished unexpectedly without file confirmation.');
        } finally {
            fclose($handle);
        }
    }

    public function deleteFile(string $accessToken, string $fileId): void
    {
        $response = Http::withToken($accessToken)
            ->timeout(20)
            ->delete(self::DRIVE_FILES_URI.'/'.urlencode($fileId), [
                'supportsAllDrives' => 'true',
            ]);

        if ($response->status() === 404 || $response->successful()) {
            return;
        }

        throw new RuntimeException("Failed to prune remote backup '{$fileId}': ".$this->extractErrorMessage($response));
    }

    public function fetchAuthenticatedEmail(string $accessToken): ?string
    {
        $response = Http::withToken($accessToken)->timeout(15)->get(self::USERINFO_URI);
        if (!$response->successful()) {
            return null;
        }

        $email = $response->json('email');
        return is_string($email) && $email !== '' ? $email : null;
    }

    private function getOAuthAccessToken(DatabaseBackupSetting $setting): string
    {
        $clientId = $setting->effectiveClientId();
        $clientSecret = $setting->getGoogleClientSecret();
        $refreshToken = $setting->getGoogleRefreshToken();

        if (!$clientId || !$clientSecret || !$refreshToken) {
            throw new RuntimeException('Google Drive OAuth credentials (Client ID, Client Secret, and Refresh Token) are not configured.');
        }

        $response = Http::asForm()
            ->timeout(20)
            ->post(self::TOKEN_URI, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Unable to refresh Google OAuth token: '.$this->extractErrorMessage($response));
        }

        $accessToken = (string) $response->json('access_token', '');
        if ($accessToken === '') {
            throw new RuntimeException('Google OAuth refresh response returned an empty access token.');
        }

        return $accessToken;
    }

    private function getServiceAccountAccessToken(DatabaseBackupSetting $setting): string
    {
        $rawJson = $setting->getGoogleServiceAccountJson();
        if (!$rawJson) {
            throw new RuntimeException('Google Service Account JSON key is not configured.');
        }

        $credentials = json_decode($rawJson, true);
        if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new RuntimeException('Invalid Google Service Account JSON key (missing client_email or private_key).');
        }

        $now = time();
        $tokenUri = (string) ($credentials['token_uri'] ?? self::TOKEN_URI);

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claimSet = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive',
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $signingInput = $header.'.'.$claimSet;
        $signature = '';
        $signed = @openssl_sign($signingInput, $signature, (string) $credentials['private_key'], OPENSSL_ALGO_SHA256);
        if (!$signed) {
            throw new RuntimeException('Failed to sign Google Service Account JWT using OpenSSL private key.');
        }

        $jwt = $signingInput.'.'.$this->base64UrlEncode($signature);

        $response = Http::asForm()
            ->timeout(20)
            ->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Service Account authentication failed: '.$this->extractErrorMessage($response));
        }

        $accessToken = (string) $response->json('access_token', '');
        if ($accessToken === '') {
            throw new RuntimeException('Google Service Account token endpoint returned empty access_token.');
        }

        return $accessToken;
    }

    private function extractErrorMessage(Response $response): string
    {
        $json = $response->json();
        $apiMessage = $json['error']['message']
            ?? $json['error_description']
            ?? (is_string($json['error'] ?? null) ? $json['error'] : null)
            ?? $response->body();

        $msg = trim((string) $apiMessage);
        if (str_contains($msg, 'storageQuotaExceeded')) {
            return $msg.' (Note: Service Accounts cannot upload to personal @gmail.com My Drive; use OAuth 2.0 mode or a Google Workspace Shared Drive.)';
        }

        return $msg !== '' ? $msg : "HTTP {$response->status()}";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
