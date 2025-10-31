<?php

namespace App\Services\Webhook;

use Illuminate\Http\Request;

class WebhookAuthenticator
{
    public static function authenticate(Request $request, array $authConfig, string $authType): bool
    {
        switch ($authType) {
            case 'none':
                return true;

            case 'basic':
                return self::authenticateBasic($request, $authConfig);

            case 'bearer':
                return self::authenticateBearer($request, $authConfig);

            case 'api_key':
                return self::authenticateApiKey($request, $authConfig);

            case 'hmac':
                return self::authenticateHmac($request, $authConfig);

            default:
                return false;
        }
    }

    private static function authenticateBasic(Request $request, array $config): bool
    {
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        if (! $request->getUser() || ! $request->getPassword()) {
            return false;
        }

        return $request->getUser() === $username && $request->getPassword() === $password;
    }

    private static function authenticateBearer(Request $request, array $config): bool
    {
        $expectedToken = $config['token'] ?? '';
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        $token = substr($authHeader, 7);

        return hash_equals($expectedToken, $token);
    }

    private static function authenticateApiKey(Request $request, array $config): bool
    {
        $headerName = $config['header_name'] ?? 'X-API-Key';
        $expectedKey = $config['api_key'] ?? '';

        $providedKey = $request->header($headerName);

        if (! $providedKey) {
            return false;
        }

        return hash_equals($expectedKey, $providedKey);
    }

    private static function authenticateHmac(Request $request, array $config): bool
    {
        $secret = $config['secret'] ?? '';
        $headerName = $config['signature_header'] ?? 'X-Signature';
        $algorithm = $config['algorithm'] ?? 'sha256';

        $providedSignature = $request->header($headerName);

        if (! $providedSignature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac($algorithm, $payload, $secret);

        if (str_starts_with($providedSignature, 'sha256=')) {
            $providedSignature = substr($providedSignature, 7);
        }

        return hash_equals($expectedSignature, $providedSignature);
    }

    public static function validateIpWhitelist(Request $request, ?array $whitelist): bool
    {
        if (empty($whitelist)) {
            return true;
        }

        $clientIp = $request->ip();

        foreach ($whitelist as $allowedIp) {
            if (self::ipMatch($clientIp, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    private static function ipMatch(string $clientIp, string $pattern): bool
    {
        if ($clientIp === $pattern) {
            return true;
        }

        if (str_contains($pattern, '/')) {
            return self::ipInCidr($clientIp, $pattern);
        }

        if (str_contains($pattern, '*')) {
            $regex = '/^'.str_replace(['.', '*'], ['\.', '.*'], $pattern).'$/';

            return (bool) preg_match($regex, $clientIp);
        }

        return false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int) $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
