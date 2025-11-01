<?php

namespace App\Services\Variable;

use App\Models\Variable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class VariableResolver
{
    public static function resolveInString(string $text, string $orgId): string
    {
        return preg_replace_callback('/\{\{\s*\$vars?\.([^\s}]+)\s*\}\}/', function ($matches) use ($orgId) {
            $variableName = $matches[1];

            return self::getVariable($variableName, $orgId) ?? $matches[0];
        }, $text);
    }

    public static function resolveInArray(array $data, string $orgId): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = self::resolveInString($value, $orgId);
            } elseif (is_array($value)) {
                $data[$key] = self::resolveInArray($value, $orgId);
            }
        }

        return $data;
    }

    public static function getVariable(string $name, string $orgId): ?string
    {
        $cacheKey = "variable:{$orgId}:{$name}";

        return Cache::remember($cacheKey, 300, function () use ($name, $orgId) {
            $variable = Variable::where('org_id', $orgId)
                ->where('name', $name)
                ->first();

            if (!$variable) {
                Log::debug('Variable not found', [
                    'variable' => $name,
                    'org_id' => $orgId,
                ]);

                return null;
            }

            if ($variable->is_secret) {
                try {
                    return Crypt::decryptString($variable->encrypted_value);
                } catch (\Exception $e) {
                    Log::error('Failed to decrypt secret variable', [
                        'variable' => $name,
                        'error' => $e->getMessage(),
                    ]);

                    return null;
                }
            }

            return $variable->value;
        });
    }

    public static function getAllVariables(string $orgId): array
    {
        return Cache::remember("variables:{$orgId}", 300, function () use ($orgId) {
            $variables = Variable::where('org_id', $orgId)->get();

            $result = [];
            foreach ($variables as $variable) {
                if ($variable->is_secret) {
                    try {
                        $result[$variable->name] = Crypt::decryptString($variable->encrypted_value);
                    } catch (\Exception $e) {
                        Log::error('Failed to decrypt secret variable', [
                            'variable' => $variable->name,
                        ]);
                        $result[$variable->name] = null;
                    }
                } else {
                    $result[$variable->name] = $variable->value;
                }
            }

            return $result;
        });
    }

    public static function clearCache(string $orgId): void
    {
        Cache::forget("variables:{$orgId}");

        $variables = Variable::where('org_id', $orgId)->get();
        foreach ($variables as $variable) {
            Cache::forget("variable:{$orgId}:{$variable->name}");
        }
    }
}
