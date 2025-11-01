<?php

namespace App\Services\Node\Execution;

use App\Services\Credential\CredentialResolver;
use App\Services\Node\Execution\Traits\ResolvesVariables;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpRequestNodeExecutor extends NodeExecutor
{
    use ResolvesVariables;

    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $orgId = $this->workflowExecution->org_id;

        $url = $this->resolveVariables($properties['url'] ?? '', $orgId);
        $url = $this->replacePlaceholders($url, $inputData);

        $method = strtolower($properties['method'] ?? 'get');

        $headers = $this->resolveVariables($properties['headers'] ?? [], $orgId);
        $headers = $this->replacePlaceholders($headers, $inputData);

        $body = $this->resolveVariables($properties['body'] ?? [], $orgId);
        $body = $this->replacePlaceholders($body, $inputData);

        $credentialId = $properties['credential_id'] ?? null;

        Log::debug('HTTP Request', [
            'url' => $url,
            'method' => $method,
            'has_credential' => !empty($credentialId),
        ]);

        $credentials = CredentialResolver::resolveForHttp($credentialId);
        $headers = array_merge($headers, $credentials['headers']);

        $httpClient = Http::withHeaders($headers);

        if ($credentials['auth']) {
            $httpClient = $httpClient->withBasicAuth($credentials['auth'][0], $credentials['auth'][1]);
        }

        if (isset($properties['timeout'])) {
            $httpClient = $httpClient->timeout($properties['timeout']);
        }

        try {
            $response = $httpClient->$method($url, $body);

            return [
                'status' => $response->status(),
                'statusText' => $response->reason(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('HTTP Request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("HTTP Request failed: {$e->getMessage()}");
        }
    }

    private function replacePlaceholders($data, array $inputData)
    {
        if (is_string($data)) {
            return $this->replaceStringPlaceholders($data, $inputData);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value, $inputData);
            }
        }

        return $data;
    }

    private function replaceStringPlaceholders(string $string, array $inputData): string
    {
        return preg_replace_callback('/{{\s*\$json\.([^\s}]+)\s*}}/', function ($matches) use ($inputData) {
            return data_get($inputData, $matches[1]);
        }, $string);
    }
}
