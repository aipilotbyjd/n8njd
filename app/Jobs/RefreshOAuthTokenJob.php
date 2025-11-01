<?php

namespace App\Jobs;

use App\Models\Credential;
use App\Services\Credential\CredentialService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshOAuthTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $credentialId;

    public function __construct(string $credentialId)
    {
        $this->credentialId = $credentialId;
    }

    public function handle(CredentialService $credentialService): void
    {
        $credential = Credential::find($this->credentialId);

        if (!$credential) {
            Log::warning('Credential not found for OAuth refresh', ['id' => $this->credentialId]);

            return;
        }

        if ($credential->type !== 'oauth2') {
            return;
        }

        $data = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($credential->encrypted_data), true);
        $expiresAt = $data['expires_at'] ?? null;

        if (!$expiresAt) {
            return;
        }

        $expiryTime = \Carbon\Carbon::parse($expiresAt);
        $now = now();

        if ($expiryTime->greaterThan($now->addMinutes(10))) {
            return;
        }

        Log::info('Refreshing OAuth token', ['credential_id' => $this->credentialId]);

        $result = $credentialService->oauthRefresh($this->credentialId);

        if ($result['status'] === 'success') {
            Log::info('OAuth token refreshed successfully', ['credential_id' => $this->credentialId]);
        } else {
            Log::error('OAuth token refresh failed', [
                'credential_id' => $this->credentialId,
                'error' => $result['message'],
            ]);
        }
    }
}
