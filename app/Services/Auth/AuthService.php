<?php

namespace App\Services\Auth;

use Aacotroneo\Saml2\Facades\Saml2Auth;
use App\Models\ApiKey;
use App\Models\Organization;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $organization = Organization::create([
                'name' => $data['organization_name'] ?? $data['name'] . "'s Workspace",
                'plan' => 'free',
                'is_active' => true,
            ]);

            $organization->users()->attach($user->id, [
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->accessToken;

            return [
                'user' => $user,
                'organization' => $organization,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];
        });
    }



    public function login(array $data): ?array
    {
        if (!Auth::attempt($data)) {
            return null;
        }

        $user = User::where('email', $data['email'])->firstOrFail();

        // Passport syntax
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function refresh(User $user): array
    {
        $user->tokens()->delete();

        // Passport syntax
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function verifyEmail(string $id, string $hash): bool
    {
        $user = User::find($id);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return false;
        }

        if ($user->hasVerifiedEmail()) {
            return true;
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return true;
    }

    public function resendVerification(string $email): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user || $user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }

    public function forgotPassword(string $email): ?string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        $token = Str::random(60);

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        return $token;
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        $passwordReset = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) {
            return false;
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->password = Hash::make($password);
        $user->save();

        DB::table('password_resets')->where('email', $email)->delete();

        return true;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return true;
    }

    public function oauthRedirect(string $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function oauthCallback(string $provider): ?array
    {
        try {
            $socialiteUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return null;
        }

        $user = User::where('email', $socialiteUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
            ]);
        }

        // Passport syntax
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function samlLogin()
    {
        return Saml2Auth::redirect('/');
    }

    public function samlAcs(): ?array
    {
        $saml2User = Saml2Auth::getSaml2User();
        $attributes = $saml2User->getAttributes();

        $email = $attributes['urn:oid:0.9.2342.19200300.100.1.3'][0];
        $name = $attributes['urn:oid:2.5.4.42'][0] . ' ' . $attributes['urn:oid:2.5.4.4'][0];

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
            ]);
        }

        // Passport syntax
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->accessToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function mfaEnable(User $user): array
    {
        $secret = Google2FA::generateSecretKey();

        $user->two_factor_secret = $secret;
        $user->save();

        $qrCodeUrl = Google2FA::getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return [
            'qr_code_url' => $qrCodeUrl,
            'secret' => $secret,
        ];
    }

    public function mfaVerify(User $user, string $oneTimePassword): bool
    {
        $isValid = Google2FA::verifyKey($user->two_factor_secret, $oneTimePassword);

        if ($isValid) {
            $user->two_factor_enabled = true;
            $user->save();
        }

        return $isValid;
    }

    public function mfaDisable(User $user): void
    {
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->save();
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user;
    }

    public function deleteAccount(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }

    public function getSessions(User $user)
    {
        return DB::table('sessions')->where('user_id', $user->id)->get();
    }

    public function deleteSession(User $user, string $sessionId): bool
    {
        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$session) {
            return false;
        }

        DB::table('sessions')->where('id', $sessionId)->delete();

        return true;
    }

    public function getApiKeys(User $user)
    {
        return $user->apiKeys;
    }

    public function createApiKey(User $user, string $name): ApiKey
    {
        $apiKey = new ApiKey([
            'name' => $name,
            'key' => Str::random(40),
        ]);

        $user->apiKeys()->save($apiKey);

        return $apiKey;
    }

    public function deleteApiKey(User $user, string $apiKeyId): bool
    {
        $apiKey = $user->apiKeys()->find($apiKeyId);

        if (!$apiKey) {
            return false;
        }

        return $apiKey->delete();
    }
}
