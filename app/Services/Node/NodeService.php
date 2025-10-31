<?php

namespace App\Services\Node;

use App\Models\Node;
use Illuminate\Support\Str;

class NodeService
{
    public function getNodes()
    {
        return Node::where('is_custom', false)->get();
    }

    public function getNodeByType(string $type): ?Node
    {
        return Node::where('type', $type)->first();
    }

    public function getCustomNodes(string $orgId)
    {
        return Node::where('is_custom', true)->where('org_id', $orgId)->get();
    }

    public function createCustomNode(array $data, string $orgId, string $userId): Node
    {
        $data['id'] = Str::uuid();
        $data['is_custom'] = true;
        $data['org_id'] = $orgId;
        $data['user_id'] = $userId;

        return Node::create($data);
    }

    public function updateCustomNode(string $id, array $data): ?Node
    {
        $node = Node::find($id);

        if (! $node || ! $node->is_custom) {
            return null;
        }

        $node->update($data);

        return $node;
    }

    public function deleteCustomNode(string $id): bool
    {
        $node = Node::find($id);

        if (! $node || ! $node->is_custom) {
            return false;
        }

        return $node->delete();
    }

    public function publishCustomNode(string $id)
    {
        $node = Node::find($id);

        if (! $node || ! $node->is_custom) {
            return ['status' => 'error', 'message' => 'Custom node not found'];
        }

        $node->published = true;
        $node->save();

        return ['status' => 'success', 'message' => 'Node published successfully'];
    }

    public function getCategories()
    {
        return [
            ['id' => 'trigger', 'name' => 'Triggers', 'icon' => 'zap'],
            ['id' => 'action', 'name' => 'Actions', 'icon' => 'play'],
            ['id' => 'logic', 'name' => 'Logic', 'icon' => 'git-branch'],
            ['id' => 'data', 'name' => 'Data Transform', 'icon' => 'shuffle'],
            ['id' => 'database', 'name' => 'Database', 'icon' => 'database'],
            ['id' => 'communication', 'name' => 'Communication', 'icon' => 'message-square'],
            ['id' => 'utility', 'name' => 'Utilities', 'icon' => 'tool'],
        ];
    }

    public function getTags()
    {
        return [
            'popular', 'new', 'advanced', 'basic',
            'http', 'email', 'database', 'file',
            'json', 'xml', 'api', 'webhook',
        ];
    }

    public function getUsageStats()
    {
        $nodes = Node::where('is_custom', false)->get();

        return $nodes->map(function ($node) {
            return [
                'type' => $node->type,
                'name' => $node->name,
                'usage_count' => 0,
            ];
        });
    }

    public function getSchema(string $type)
    {
        $schemas = [
            'http-request' => [
                'properties' => [
                    ['name' => 'url', 'type' => 'string', 'required' => true],
                    ['name' => 'method', 'type' => 'select', 'options' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], 'default' => 'GET'],
                    ['name' => 'headers', 'type' => 'keyvalue', 'required' => false],
                    ['name' => 'body', 'type' => 'json', 'required' => false],
                    ['name' => 'credential_id', 'type' => 'credential', 'required' => false],
                    ['name' => 'timeout', 'type' => 'number', 'default' => 30],
                ],
            ],
            'email' => [
                'properties' => [
                    ['name' => 'to', 'type' => 'string', 'required' => true],
                    ['name' => 'subject', 'type' => 'string', 'required' => true],
                    ['name' => 'body', 'type' => 'text', 'required' => true],
                    ['name' => 'credential_id', 'type' => 'credential', 'required' => false],
                ],
            ],
            'if' => [
                'properties' => [
                    ['name' => 'condition', 'type' => 'expression', 'required' => true],
                ],
            ],
            'loop' => [
                'properties' => [
                    ['name' => 'mode', 'type' => 'select', 'options' => ['forEach', 'times'], 'default' => 'forEach'],
                    ['name' => 'items_key', 'type' => 'string', 'required' => false],
                    ['name' => 'times', 'type' => 'number', 'required' => false],
                ],
            ],
            'database' => [
                'properties' => [
                    ['name' => 'operation', 'type' => 'select', 'options' => ['select', 'insert', 'update', 'delete'], 'required' => true],
                    ['name' => 'query', 'type' => 'text', 'required' => true],
                    ['name' => 'credential_id', 'type' => 'credential', 'required' => true],
                ],
            ],
        ];

        return $schemas[$type] ?? ['properties' => []];
    }

    public function testNode(string $type, array $config)
    {
        try {
            $schema = $this->getSchema($type);

            if (empty($schema['properties'])) {
                return ['status' => 'error', 'message' => 'Unknown node type'];
            }

            foreach ($schema['properties'] as $prop) {
                if ($prop['required'] && ! isset($config[$prop['name']])) {
                    return [
                        'status' => 'error',
                        'message' => "Missing required property: {$prop['name']}",
                    ];
                }
            }

            return ['status' => 'success', 'message' => 'Node configuration is valid'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function validateConfig(string $type, array $config)
    {
        return $this->testNode($type, $config);
    }

    public function getDynamicParameters(string $type)
    {
        return [];
    }

    public function resolveParameters(string $type, array $parameters)
    {
        return $parameters;
    }

    public function getNodeUsage(string $type)
    {
        return [
            'total_workflows' => 0,
            'total_executions' => 0,
            'avg_execution_time_ms' => 0,
        ];
    }
}
