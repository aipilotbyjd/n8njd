<?php

namespace App\Services\Credential;

use App\Models\Credential;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CredentialResolver
{
    public static function resolve(?string $credentialId): ?array
    {
        if (!$credentialId) {
            return null;
        }

        $credential = Credential::find($credentialId);

        if (!$credential) {
            Log::warning('Credential not found', ['credential_id' => $credentialId]);

            return null;
        }

        try {
            $decryptedData = Crypt::decryptString($credential->encrypted_data);

            return json_decode($decryptedData, true);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt credential', [
                'credential_id' => $credentialId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function resolveForHttp(?string $credentialId): array
    {
        $credentials = self::resolve($credentialId);

        if (!$credentials) {
            return [
                'headers' => [],
                'auth' => null,
            ];
        }

        $type = $credentials['type'] ?? 'none';

        switch ($type) {
            case 'basic':
                return [
                    'headers' => [],
                    'auth' => [
                        $credentials['username'] ?? '',
                        $credentials['password'] ?? '',
                    ],
                ];

            case 'bearer':
                return [
                    'headers' => [
                        'Authorization' => 'Bearer ' . ($credentials['token'] ?? ''),
                    ],
                    'auth' => null,
                ];

            case 'api_key':
                $headerName = $credentials['header_name'] ?? 'X-API-Key';

                return [
                    'headers' => [
                        $headerName => $credentials['api_key'] ?? '',
                    ],
                    'auth' => null,
                ];

            case 'oauth2':
                return [
                    'headers' => [
                        'Authorization' => 'Bearer ' . ($credentials['access_token'] ?? ''),
                    ],
                    'auth' => null,
                ];

            default:
                return [
                    'headers' => [],
                    'auth' => null,
                ];
        }
    }

    public static function resolveForEmail(?string $credentialId): array
    {
        $credentials = self::resolve($credentialId);

        if (!$credentials) {
            return [
                'from' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'smtp' => null,
            ];
        }

        $type = $credentials['type'] ?? 'smtp';

        if ($type === 'smtp') {
            return [
                'from' => $credentials['from_email'] ?? config('mail.from.address'),
                'from_name' => $credentials['from_name'] ?? config('mail.from.name'),
                'smtp' => [
                    'host' => $credentials['host'] ?? '',
                    'port' => $credentials['port'] ?? 587,
                    'encryption' => $credentials['encryption'] ?? 'tls',
                    'username' => $credentials['username'] ?? '',
                    'password' => $credentials['password'] ?? '',
                ],
            ];
        }

        return [
            'from' => $credentials['from_email'] ?? config('mail.from.address'),
            'from_name' => $credentials['from_name'] ?? config('mail.from.name'),
            'smtp' => null,
        ];
    }
}
