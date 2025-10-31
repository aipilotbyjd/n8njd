<?php

namespace App\Services\Node\Execution;

use App\Services\Credential\CredentialResolver;
use App\Services\Node\Execution\Traits\ResolvesVariables;
use Illuminate\Support\Facades\Log;

class DatabaseNodeExecutor extends NodeExecutor
{
    use ResolvesVariables;

    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;
        $orgId = $this->workflowExecution->org_id;

        $operation = $properties['operation'] ?? 'select';
        $query = $this->resolveVariables($properties['query'] ?? '', $orgId);
        $query = $this->replacePlaceholders($query, $inputData);
        $credentialId = $properties['credential_id'] ?? null;

        if (! $credentialId) {
            throw new \Exception('Database credential is required');
        }

        $credentials = CredentialResolver::resolve($credentialId);

        if (! $credentials) {
            throw new \Exception('Database credential not found');
        }

        Log::debug('Database operation', [
            'operation' => $operation,
            'type' => $credentials['type'] ?? 'unknown',
        ]);

        $type = $credentials['type'] ?? 'mysql';

        try {
            if ($type === 'mysql' || $type === 'postgresql') {
                return $this->executeSql($type, $credentials, $query, $operation);
            } elseif ($type === 'mongodb') {
                return $this->executeMongo($credentials, $query, $operation);
            } else {
                throw new \Exception("Unsupported database type: {$type}");
            }
        } catch (\Exception $e) {
            Log::error('Database operation failed', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);

            throw new \Exception("Database operation failed: {$e->getMessage()}");
        }
    }

    private function executeSql(string $type, array $credentials, string $query, string $operation): array
    {
        $dsn = "{$type}:host={$credentials['host']};port={$credentials['port']};dbname={$credentials['database']}";
        $pdo = new \PDO($dsn, $credentials['username'], $credentials['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        if ($operation === 'select') {
            $statement = $pdo->query($query);
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'rows' => $results,
                'count' => count($results),
                'operation' => 'select',
            ];
        }

        $affectedRows = $pdo->exec($query);

        return [
            'affected_rows' => $affectedRows,
            'operation' => $operation,
        ];
    }

    private function executeMongo(array $credentials, string $query, string $operation): array
    {
        if (! class_exists('MongoDB\\Client')) {
            throw new \Exception('MongoDB extension not installed. Run: composer require mongodb/mongodb');
        }

        $connectionString = "mongodb://{$credentials['username']}:{$credentials['password']}@{$credentials['host']}:{$credentials['port']}/{$credentials['database']}";
        $client = new \MongoDB\Client($connectionString);
        $database = $client->selectDatabase($credentials['database']);

        $queryData = json_decode($query, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON query for MongoDB');
        }

        $collection = $database->selectCollection($queryData['collection'] ?? 'default');

        switch ($operation) {
            case 'select':
                $filter = $queryData['filter'] ?? [];
                $options = $queryData['options'] ?? [];
                $cursor = $collection->find($filter, $options);
                $results = iterator_to_array($cursor);

                return [
                    'documents' => $results,
                    'count' => count($results),
                    'operation' => 'find',
                ];

            case 'insert':
                $document = $queryData['document'] ?? [];
                $result = $collection->insertOne($document);

                return [
                    'inserted_id' => (string) $result->getInsertedId(),
                    'operation' => 'insertOne',
                ];

            case 'update':
                $filter = $queryData['filter'] ?? [];
                $update = $queryData['update'] ?? [];
                $result = $collection->updateMany($filter, $update);

                return [
                    'matched_count' => $result->getMatchedCount(),
                    'modified_count' => $result->getModifiedCount(),
                    'operation' => 'updateMany',
                ];

            case 'delete':
                $filter = $queryData['filter'] ?? [];
                $result = $collection->deleteMany($filter);

                return [
                    'deleted_count' => $result->getDeletedCount(),
                    'operation' => 'deleteMany',
                ];

            default:
                throw new \Exception("Unsupported MongoDB operation: {$operation}");
        }
    }
}
