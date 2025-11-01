<?php

namespace App\Console\Commands;

use App\Jobs\RefreshOAuthTokenJob;
use App\Models\Credential;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class RefreshExpiredOAuthTokens extends Command
{
    protected $signature = 'oauth:refresh-tokens';

    protected $description = 'Refresh OAuth tokens that are expiring soon';

    public function handle()
    {
        $this->info('Checking for OAuth tokens to refresh...');

        $credentials = Credential::where('type', 'oauth2')->get();

        $refreshed = 0;

        foreach ($credentials as $credential) {
            try {
                $data = json_decode(Crypt::decryptString($credential->encrypted_data), true);
                $expiresAt = $data['expires_at'] ?? null;
                $refreshToken = $data['refresh_token'] ?? null;

                if (!$expiresAt || !$refreshToken) {
                    continue;
                }

                $expiryTime = Carbon::parse($expiresAt);
                $now = now();

                if ($expiryTime->lessThan($now->addHours(1))) {
                    $this->info("Queueing refresh for credential: {$credential->id}");
                    RefreshOAuthTokenJob::dispatch($credential->id);
                    $refreshed++;
                }
            } catch (\Exception $e) {
                $this->error("Error processing credential {$credential->id}: {$e->getMessage()}");
            }
        }

        $this->info("Queued {$refreshed} OAuth token(s) for refresh.");

        return 0;
    }
}
