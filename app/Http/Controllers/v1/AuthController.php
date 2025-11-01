<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\V1\Auth\CreateApiKeyRequest;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\MfaVerifyRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Requests\V1\Auth\ResendVerificationRequest;
use App\Http\Requests\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\V1\Auth\UpdateProfileRequest;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $data = $this->authService->register($request->validated());

        return $this->created($data, 'User registered successfully.');
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request->validated());

        if (!$data) {
            return $this->unauthorized('Invalid credentials');
        }

        return $this->success($data, 'User logged in successfully.');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->success([], 'User logged out successfully.');
    }

    public function refresh(Request $request)
    {
        $data = $this->authService->refresh($request->user());

        return $this->success($data, 'Token refreshed successfully.');
    }

    public function verifyEmail(Request $request)
    {
        if (!$this->authService->verifyEmail($request->route('id'), $request->route('hash'))) {
            return $this->unauthorized('Invalid verification link');
        }

        return $this->success([], 'Email verified successfully.');
    }

    public function resendVerification(ResendVerificationRequest $request)
    {
        if (!$this->authService->resendVerification($request->email)) {
            return $this->unprocessable('User not found or email already verified.');
        }

        return $this->success([], 'Verification link sent successfully.');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $token = $this->authService->forgotPassword($request->email);

        if (!$token) {
            return $this->notFound('User not found');
        }

        return $this->success(['reset_token' => $token], 'Password reset token generated successfully.');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        if (!$this->authService->resetPassword($request->email, $request->token, $request->password)) {
            return $this->unauthorized('Invalid token');
        }

        return $this->success([], 'Password reset successfully.');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        if (!$this->authService->changePassword($request->user(), $request->current_password, $request->new_password)) {
            return $this->unauthorized('Invalid current password');
        }

        return $this->success([], 'Password changed successfully.');
    }

    public function oauthRedirect($provider)
    {
        return $this->authService->oauthRedirect($provider);
    }

    public function oauthCallback($provider)
    {
        $data = $this->authService->oauthCallback($provider);

        if (!$data) {
            return $this->unauthorized('Failed to authenticate with ' . $provider);
        }

        return $this->success($data, 'User authenticated successfully.');
    }

    public function samlLogin(Request $request)
    {
        return $this->authService->samlLogin();
    }

    public function samlAcs(Request $request)
    {
        $data = $this->authService->samlAcs();

        if (!$data) {
            return $this->unauthorized('Failed to authenticate with SAML');
        }

        return $this->success($data, 'User authenticated successfully.');
    }

    public function mfaEnable(Request $request)
    {
        $data = $this->authService->mfaEnable($request->user());

        return $this->success($data, 'MFA enabled successfully. Scan the QR code with your authenticator app.');
    }

    public function mfaVerify(MfaVerifyRequest $request)
    {
        if (!$this->authService->mfaVerify($request->user(), $request->one_time_password)) {
            return $this->unauthorized('Invalid one time password');
        }

        return $this->success([], 'MFA verified successfully.');
    }

    public function mfaDisable(Request $request)
    {
        $this->authService->mfaDisable($request->user());

        return $this->success([], 'MFA disabled successfully.');
    }

    public function me(Request $request)
    {
        return $this->success($request->user());
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());

        return $this->success($user, 'Profile updated successfully.');
    }

    public function deleteAccount(Request $request)
    {
        $this->authService->deleteAccount($request->user());

        return $this->success([], 'Account deleted successfully.');
    }

    public function getSessions(Request $request)
    {
        return $this->success($this->authService->getSessions($request->user()));
    }

    public function deleteSession(Request $request, $id)
    {
        if (!$this->authService->deleteSession($request->user(), $id)) {
            return $this->notFound('Session not found');
        }

        return $this->success([], 'Session deleted successfully.');
    }

    public function getApiKeys(Request $request)
    {
        return $this->success($this->authService->getApiKeys($request->user()));
    }

    public function createApiKey(CreateApiKeyRequest $request)
    {
        $apiKey = $this->authService->createApiKey($request->user(), $request->name);

        return $this->created($apiKey, 'API key created successfully.');
    }

    public function deleteApiKey(Request $request, $id)
    {
        if (!$this->authService->deleteApiKey($request->user(), $id)) {
            return $this->notFound('API key not found');
        }

        return $this->success([], 'API key deleted successfully.');
    }
}
