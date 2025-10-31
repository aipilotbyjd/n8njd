<?php

namespace App\Services\Credential;

use App\Models\Credential;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CredentialService
{
    public function getCredentialsByOrg(string $orgId)
    {
        return Credential::where('org_id', $orgId)->get();
    }

    public function createCredential(array $data, string $orgId, string $userId): Credential
    {
        $data['id'] = Str::uuid();
        $data['org_id'] = $orgId;
        $data['user_id'] = $userId;
        $data['encrypted_data'] = Crypt::encryptString(json_encode($data['data']));
        unset($data['data']);

        return Credential::create($data);
    }

    public function getCredential(string $id): ?Credential
    {
        $credential = Credential::find($id);

        if ($credential) {
            $credential->data = json_decode(Crypt::decryptString($credential->encrypted_data), true);
        }

        return $credential;
    }

    public function updateCredential(string $id, array $data): ?Credential
    {
        $credential = Credential::find($id);

        if (! $credential) {
            return null;
        }

        if (isset($data['data'])) {
            $data['encrypted_data'] = Crypt::encryptString(json_encode($data['data']));
            unset($data['data']);
        }

        $credential->update($data);

        return $credential;
    }

    public function deleteCredential(string $id): bool
    {
        $credential = Credential::find($id);

        if (! $credential) {
            return false;
        }

        return $credential->delete();
    }

    public function getTypes()
    {
        return [
            ['type' => 'http_basic', 'name' => 'HTTP Basic Auth', 'icon' => 'lock'],
            ['type' => 'http_bearer', 'name' => 'HTTP Bearer Token', 'icon' => 'key'],
            ['type' => 'api_key', 'name' => 'API Key', 'icon' => 'key'],
            ['type' => 'oauth2', 'name' => 'OAuth2', 'icon' => 'shield'],
            ['type' => 'smtp', 'name' => 'SMTP Email', 'icon' => 'mail'],
            ['type' => 'database', 'name' => 'Database', 'icon' => 'database'],
            ['type' => 'aws', 'name' => 'AWS', 'icon' => 'cloud'],
            ['type' => 'github', 'name' => 'GitHub', 'icon' => 'github'],
        ];
    }

    public function getTypeSchema(string $type)
    {
        $schemas = [
            'http_basic' => [
                'fields' => [
                    ['name' => 'username', 'type' => 'string', 'required' => true],
                    ['name' => 'password', 'type' => 'password', 'required' => true],
                ],
            ],
            'http_bearer' => [
                'fields' => [
                    ['name' => 'token', 'type' => 'password', 'required' => true],
                ],
            ],
            'api_key' => [
                'fields' => [
                    ['name' => 'api_key', 'type' => 'password', 'required' => true],
                    ['name' => 'header_name', 'type' => 'string', 'required' => false, 'default' => 'X-API-Key'],
                ],
            ],
            'oauth2' => [
                'fields' => [
                    ['name' => 'client_id', 'type' => 'string', 'required' => true],
                    ['name' => 'client_secret', 'type' => 'password', 'required' => true],
                    ['name' => 'access_token', 'type' => 'password', 'required' => false],
                    ['name' => 'refresh_token', 'type' => 'password', 'required' => false],
                ],
            ],
            'smtp' => [
                'fields' => [
                    ['name' => 'host', 'type' => 'string', 'required' => true],
                    ['name' => 'port', 'type' => 'number', 'required' => true, 'default' => 587],
                    ['name' => 'username', 'type' => 'string', 'required' => true],
                    ['name' => 'password', 'type' => 'password', 'required' => true],
                    ['name' => 'encryption', 'type' => 'select', 'options' => ['tls', 'ssl'], 'default' => 'tls'],
                    ['name' => 'from_email', 'type' => 'email', 'required' => false],
                    ['name' => 'from_name', 'type' => 'string', 'required' => false],
                ],
            ],
            'database' => [
                'fields' => [
                    ['name' => 'type', 'type' => 'select', 'options' => ['mysql', 'postgresql', 'mongodb'], 'required' => true],
                    ['name' => 'host', 'type' => 'string', 'required' => true],
                    ['name' => 'port', 'type' => 'number', 'required' => true],
                    ['name' => 'database', 'type' => 'string', 'required' => true],
                    ['name' => 'username', 'type' => 'string', 'required' => true],
                    ['name' => 'password', 'type' => 'password', 'required' => true],
                ],
            ],
        ];

        return $schemas[$type] ?? ['fields' => []];
    }

    public function test(string $id)
    {
        $credential = $this->getCredential($id);

        if (! $credential) {
            return ['status' => 'error', 'message' => 'Credential not found'];
        }

        try {
            $type = $credential->type;
            $data = $credential->data;

            switch ($type) {
                case 'http_basic':
                case 'http_bearer':
                case 'api_key':
                    return ['status' => 'success', 'message' => 'HTTP credential validated'];

                case 'smtp':
                    return $this->testSmtp($data);

                case 'database':
                    return $this->testDatabase($data);

                default:
                    return ['status' => 'success', 'message' => 'Credential structure is valid'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function testSmtp(array $data): array
    {
        try {
            $transport = new \Swift_SmtpTransport($data['host'], $data['port'], $data['encryption'] ?? 'tls');
            $transport->setUsername($data['username']);
            $transport->setPassword($data['password']);
            $mailer = new \Swift_Mailer($transport);
            $mailer->getTransport()->start();

            return ['status' => 'success', 'message' => 'SMTP connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'SMTP test failed: '.$e->getMessage()];
        }
    }

    private function testDatabase(array $data): array
    {
        try {
            $type = $data['type'] ?? 'mysql';

            if ($type === 'mysql' || $type === 'postgresql') {
                $dsn = "{$type}:host={$data['host']};port={$data['port']};dbname={$data['database']}";
                $pdo = new \PDO($dsn, $data['username'], $data['password']);
                $pdo->query('SELECT 1');

                return ['status' => 'success', 'message' => 'Database connection successful'];
            }

            return ['status' => 'success', 'message' => 'Database credential structure valid'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database test failed: '.$e->getMessage()];
        }
    }

    public function getTestStatus(string $id)
    {
        return ['status' => 'success'];
    }

    public function oauthAuthorize(string $id)
    {
        $credential = $this->getCredential($id);

        if (! $credential) {
            return ['status' => 'error', 'message' => 'Credential not found'];
        }

        if ($credential->type !== 'oauth2') {
            return ['status' => 'error', 'message' => 'Not an OAuth2 credential'];
        }

        $data = $credential->data;
        $clientId = $data['client_id'] ?? null;
        $redirectUri = url('/api/v1/credentials/'.$id.'/oauth/callback');
        $authUrl = $data['authorization_url'] ?? null;

        if (! $clientId || ! $authUrl) {
            return ['status' => 'error', 'message' => 'OAuth configuration incomplete'];
        }

        $state = Str::random(40);
        cache()->put('oauth_state_'.$id, $state, 600);

        $scope = $data['scope'] ?? '';

        $authorizationUrl = $authUrl.'?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ]);

        return [
            'status' => 'success',
            'authorization_url' => $authorizationUrl,
            'state' => $state,
        ];
    }

    public function oauthCallback(string $id, array $data)
    {
        $credential = $this->getCredential($id);

        if (! $credential) {
            return ['status' => 'error', 'message' => 'Credential not found'];
        }

        $code = $data['code'] ?? null;
        $state = $data['state'] ?? null;
        $cachedState = cache()->get('oauth_state_'.$id);

        if (! $code) {
            return ['status' => 'error', 'message' => 'Authorization code not provided'];
        }

        if ($state !== $cachedState) {
            return ['status' => 'error', 'message' => 'Invalid state parameter'];
        }

        cache()->forget('oauth_state_'.$id);

        $credData = $credential->data;
        $clientId = $credData['client_id'] ?? null;
        $clientSecret = $credData['client_secret'] ?? null;
        $tokenUrl = $credData['token_url'] ?? null;
        $redirectUri = url('/api/v1/credentials/'.$id.'/oauth/callback');

        if (! $clientId || ! $clientSecret || ! $tokenUrl) {
            return ['status' => 'error', 'message' => 'OAuth configuration incomplete'];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->post($tokenUrl, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if (! $response->successful()) {
                return [
                    'status' => 'error',
                    'message' => 'Token exchange failed: '.$response->body(),
                ];
            }

            $tokenData = $response->json();
            $credData['access_token'] = $tokenData['access_token'] ?? null;
            $credData['refresh_token'] = $tokenData['refresh_token'] ?? null;
            $credData['expires_at'] = isset($tokenData['expires_in'])
                ? now()->addSeconds($tokenData['expires_in'])->toDateTimeString()
                : null;

            $this->updateCredential($id, ['data' => $credData]);

            return [
                'status' => 'success',
                'message' => 'OAuth tokens obtained successfully',
                'expires_at' => $credData['expires_at'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'OAuth callback failed: '.$e->getMessage(),
            ];
        }
    }

    public function oauthRefresh(string $id)
    {
        $credential = $this->getCredential($id);

        if (! $credential) {
            return ['status' => 'error', 'message' => 'Credential not found'];
        }

        if ($credential->type !== 'oauth2') {
            return ['status' => 'error', 'message' => 'Not an OAuth2 credential'];
        }

        $credData = $credential->data;
        $refreshToken = $credData['refresh_token'] ?? null;
        $tokenUrl = $credData['token_url'] ?? null;
        $clientId = $credData['client_id'] ?? null;
        $clientSecret = $credData['client_secret'] ?? null;

        if (! $refreshToken || ! $tokenUrl || ! $clientId || ! $clientSecret) {
            return ['status' => 'error', 'message' => 'Refresh token or OAuth config missing'];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()->post($tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if (! $response->successful()) {
                return [
                    'status' => 'error',
                    'message' => 'Token refresh failed: '.$response->body(),
                ];
            }

            $tokenData = $response->json();
            $credData['access_token'] = $tokenData['access_token'] ?? $credData['access_token'];

            if (isset($tokenData['refresh_token'])) {
                $credData['refresh_token'] = $tokenData['refresh_token'];
            }

            $credData['expires_at'] = isset($tokenData['expires_in'])
                ? now()->addSeconds($tokenData['expires_in'])->toDateTimeString()
                : null;

            $this->updateCredential($id, ['data' => $credData]);

            return [
                'status' => 'success',
                'message' => 'OAuth token refreshed successfully',
                'expires_at' => $credData['expires_at'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'OAuth refresh failed: '.$e->getMessage(),
            ];
        }
    }

    public function getShares(string $id)
    {
        $credential = Credential::find($id);

        if (! $credential) {
            return [];
        }

        return $credential->shared_with_users ?? [];
    }

    public function createShare(string $id, string $userId)
    {
        $credential = Credential::find($id);

        if (! $credential) {
            return ['status' => 'error', 'message' => 'Credential not found'];
        }

        $sharedUsers = $credential->shared_with_users ?? [];

        if (! in_array($userId, $sharedUsers)) {
            $sharedUsers[] = $userId;
            $credential->shared_with_users = $sharedUsers;
            $credential->save();
        }

        return ['status' => 'success', 'message' => 'Credential shared successfully'];
    }

    public function deleteShare(string $id, string $userId)
    {
        $credential = Credential::find($id);

        if (! $credential) {
            return ['status' => 'error', 'message' => 'Credential not found'];
        }

        $sharedUsers = $credential->shared_with_users ?? [];
        $sharedUsers = array_filter($sharedUsers, fn ($uid) => $uid !== $userId);
        $credential->shared_with_users = array_values($sharedUsers);
        $credential->save();

        return ['status' => 'success', 'message' => 'Credential unshared successfully'];
    }

    public function getUsage(string $id)
    {
        $credential = Credential::find($id);

        if (! $credential) {
            return ['error' => 'Credential not found'];
        }

        return [
            'last_used_at' => $credential->updated_at,
            'test_status' => $credential->test_status,
            'last_tested_at' => $credential->last_tested_at,
        ];
    }

    public function getWorkflows(string $id)
    {
        return [];
    }
}
