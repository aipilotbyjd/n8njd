<?php

namespace App\Services\Node;

use App\Models\Node;
use App\Models\NodeType;
use Illuminate\Support\Str;

class NodeService
{
    public function getNodes()
    {
        return NodeType::active()->get();
    }

    public function getNodeByType(string $type): ?NodeType
    {
        return NodeType::findByNodeType($type);
    }

    public function getCustomNodes(string $orgId)
    {
        return NodeType::where('is_custom', true)->where('organization_id', $orgId)->get();
    }

    public function createCustomNode(array $data, string $orgId, string $userId): NodeType
    {
        $data['is_custom'] = true;
        $data['organization_id'] = $orgId;
        $data['created_by'] = $userId;

        return NodeType::create($data);
    }

    public function updateCustomNode(string $id, array $data): ?NodeType
    {
        $node = NodeType::find($id);

        if (!$node || !$node->is_custom) {
            return null;
        }

        $node->update($data);

        return $node;
    }

    public function deleteCustomNode(string $id): bool
    {
        $node = NodeType::find($id);

        if (!$node || !$node->is_custom) {
            return false;
        }

        return $node->delete();
    }

    public function publishCustomNode(string $id)
    {
        $node = Node::find($id);

        if (!$node || !$node->is_custom) {
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
        $nodeType = NodeType::findByNodeType($type);
        
        if (!$nodeType) {
            return ['properties' => []];
        }
        
        return ['properties' => $nodeType->properties ?? []];
    }

    public function testNode(string $type, array $config)
    {
        try {
            $schema = $this->getSchema($type);

            if (empty($schema['properties'])) {
                return ['status' => 'error', 'message' => 'Unknown node type'];
            }

            foreach ($schema['properties'] as $prop) {
                if ($prop['required'] && !isset($config[$prop['name']])) {
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
