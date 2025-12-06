<?php

namespace Database\Seeders;

use App\Models\NodeType;
use Illuminate\Database\Seeder;

class NodeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nodeTypes = [
            // ========== TRIGGERS ==========
            [
                'type' => 'trigger',
                'node_type' => 'webhook',
                'name' => 'Webhook',
                'description' => 'Starts the workflow when a webhook URL is called',
                'icon' => 'webhook',
                'color' => '#6366f1',
                'category' => 'trigger',
                'inputs' => [],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'method', 'displayName' => 'Method', 'type' => 'select', 'options' => ['GET', 'POST', 'PUT', 'DELETE'], 'default' => 'POST'],
                    ['name' => 'path', 'displayName' => 'Path', 'type' => 'string', 'required' => true],
                    ['name' => 'authentication', 'displayName' => 'Authentication', 'type' => 'select', 'options' => ['none', 'basic', 'header'], 'default' => 'none'],
                ],
            ],
            [
                'type' => 'trigger',
                'node_type' => 'schedule',
                'name' => 'Schedule Trigger',
                'description' => 'Starts the workflow on a schedule (cron)',
                'icon' => 'clock',
                'color' => '#8b5cf6',
                'category' => 'trigger',
                'inputs' => [],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'cron', 'displayName' => 'Cron Expression', 'type' => 'string', 'default' => '0 * * * *'],
                    ['name' => 'timezone', 'displayName' => 'Timezone', 'type' => 'string', 'default' => 'UTC'],
                ],
            ],
            [
                'type' => 'trigger',
                'node_type' => 'manual',
                'name' => 'Manual Trigger',
                'description' => 'Starts the workflow manually',
                'icon' => 'play',
                'color' => '#10b981',
                'category' => 'trigger',
                'inputs' => [],
                'outputs' => ['main'],
                'properties' => [],
            ],

            // ========== CORE / ACTIONS ==========
            [
                'type' => 'action',
                'node_type' => 'http_request',
                'name' => 'HTTP Request',
                'description' => 'Make HTTP requests to external APIs',
                'icon' => 'globe',
                'color' => '#3b82f6',
                'category' => 'action',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'url', 'displayName' => 'URL', 'type' => 'string', 'required' => true],
                    ['name' => 'method', 'displayName' => 'Method', 'type' => 'select', 'options' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], 'default' => 'GET'],
                    ['name' => 'headers', 'displayName' => 'Headers', 'type' => 'keyvalue'],
                    ['name' => 'body', 'displayName' => 'Body', 'type' => 'json'],
                    ['name' => 'timeout', 'displayName' => 'Timeout (seconds)', 'type' => 'number', 'default' => 30],
                ],
            ],
            [
                'type' => 'action',
                'node_type' => 'code',
                'name' => 'Code',
                'description' => 'Run custom JavaScript code',
                'icon' => 'code',
                'color' => '#f59e0b',
                'category' => 'action',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'code', 'displayName' => 'Code', 'type' => 'code', 'language' => 'javascript', 'required' => true],
                ],
            ],
            [
                'type' => 'action',
                'node_type' => 'set',
                'name' => 'Set',
                'description' => 'Set or modify data values',
                'icon' => 'edit',
                'color' => '#06b6d4',
                'category' => 'action',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'values', 'displayName' => 'Values', 'type' => 'keyvalue', 'required' => true],
                    ['name' => 'mode', 'displayName' => 'Mode', 'type' => 'select', 'options' => ['set', 'append', 'merge'], 'default' => 'set'],
                ],
            ],
            [
                'type' => 'action',
                'node_type' => 'email',
                'name' => 'Send Email',
                'description' => 'Send an email',
                'icon' => 'mail',
                'color' => '#ef4444',
                'category' => 'communication',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'to', 'displayName' => 'To', 'type' => 'string', 'required' => true],
                    ['name' => 'subject', 'displayName' => 'Subject', 'type' => 'string', 'required' => true],
                    ['name' => 'body', 'displayName' => 'Body', 'type' => 'text', 'required' => true],
                    ['name' => 'credential_id', 'displayName' => 'Email Credential', 'type' => 'credential'],
                ],
            ],
            [
                'type' => 'action',
                'node_type' => 'database',
                'name' => 'Database',
                'description' => 'Execute database queries',
                'icon' => 'database',
                'color' => '#22c55e',
                'category' => 'database',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'operation', 'displayName' => 'Operation', 'type' => 'select', 'options' => ['select', 'insert', 'update', 'delete'], 'required' => true],
                    ['name' => 'query', 'displayName' => 'Query', 'type' => 'text', 'required' => true],
                    ['name' => 'credential_id', 'displayName' => 'Database Credential', 'type' => 'credential', 'required' => true],
                ],
            ],

            // ========== LOGIC ==========
            [
                'type' => 'logic',
                'node_type' => 'if',
                'name' => 'IF',
                'description' => 'Route data based on conditions',
                'icon' => 'git-branch',
                'color' => '#a855f7',
                'category' => 'logic',
                'inputs' => ['main'],
                'outputs' => ['true', 'false'],
                'properties' => [
                    ['name' => 'condition', 'displayName' => 'Condition', 'type' => 'expression', 'required' => true],
                ],
            ],
            [
                'type' => 'logic',
                'node_type' => 'switch',
                'name' => 'Switch',
                'description' => 'Route data based on multiple conditions',
                'icon' => 'shuffle',
                'color' => '#ec4899',
                'category' => 'logic',
                'inputs' => ['main'],
                'outputs' => ['output_0', 'output_1', 'output_2', 'default'],
                'properties' => [
                    ['name' => 'mode', 'displayName' => 'Mode', 'type' => 'select', 'options' => ['rules', 'expression'], 'default' => 'rules'],
                    ['name' => 'rules', 'displayName' => 'Rules', 'type' => 'array'],
                ],
            ],
            [
                'type' => 'logic',
                'node_type' => 'loop',
                'name' => 'Loop',
                'description' => 'Loop over items or run a fixed number of times',
                'icon' => 'repeat',
                'color' => '#14b8a6',
                'category' => 'logic',
                'inputs' => ['main'],
                'outputs' => ['main', 'done'],
                'properties' => [
                    ['name' => 'mode', 'displayName' => 'Mode', 'type' => 'select', 'options' => ['forEach', 'times'], 'default' => 'forEach'],
                    ['name' => 'items_key', 'displayName' => 'Items Key', 'type' => 'string'],
                    ['name' => 'times', 'displayName' => 'Times', 'type' => 'number'],
                ],
            ],
            [
                'type' => 'logic',
                'node_type' => 'merge',
                'name' => 'Merge',
                'description' => 'Merge data from multiple inputs',
                'icon' => 'git-merge',
                'color' => '#6366f1',
                'category' => 'logic',
                'inputs' => ['input_0', 'input_1'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'mode', 'displayName' => 'Mode', 'type' => 'select', 'options' => ['append', 'merge', 'keepKeyMatches'], 'default' => 'append'],
                ],
            ],

            // ========== DATA TRANSFORM ==========
            [
                'type' => 'transform',
                'node_type' => 'filter',
                'name' => 'Filter',
                'description' => 'Filter items based on conditions',
                'icon' => 'filter',
                'color' => '#f97316',
                'category' => 'data',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'conditions', 'displayName' => 'Conditions', 'type' => 'array', 'required' => true],
                ],
            ],
            [
                'type' => 'transform',
                'node_type' => 'sort',
                'name' => 'Sort',
                'description' => 'Sort items by a field',
                'icon' => 'arrow-up-down',
                'color' => '#84cc16',
                'category' => 'data',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'field', 'displayName' => 'Field', 'type' => 'string', 'required' => true],
                    ['name' => 'order', 'displayName' => 'Order', 'type' => 'select', 'options' => ['asc', 'desc'], 'default' => 'asc'],
                ],
            ],
            [
                'type' => 'transform',
                'node_type' => 'split',
                'name' => 'Split',
                'description' => 'Split items into multiple outputs',
                'icon' => 'split',
                'color' => '#0ea5e9',
                'category' => 'data',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'field', 'displayName' => 'Field to Split', 'type' => 'string', 'required' => true],
                ],
            ],
            [
                'type' => 'transform',
                'node_type' => 'aggregate',
                'name' => 'Aggregate',
                'description' => 'Aggregate items (sum, count, avg, etc.)',
                'icon' => 'calculator',
                'color' => '#d946ef',
                'category' => 'data',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'operation', 'displayName' => 'Operation', 'type' => 'select', 'options' => ['count', 'sum', 'avg', 'min', 'max'], 'required' => true],
                    ['name' => 'field', 'displayName' => 'Field', 'type' => 'string'],
                ],
            ],
            [
                'type' => 'transform',
                'node_type' => 'limit',
                'name' => 'Limit',
                'description' => 'Limit the number of items',
                'icon' => 'hash',
                'color' => '#78716c',
                'category' => 'data',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'count', 'displayName' => 'Count', 'type' => 'number', 'required' => true, 'default' => 10],
                ],
            ],

            // ========== UTILITIES ==========
            [
                'type' => 'wait',
                'node_type' => 'wait',
                'name' => 'Wait',
                'description' => 'Pause the workflow for a specified time',
                'icon' => 'pause',
                'color' => '#64748b',
                'category' => 'utility',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'duration', 'displayName' => 'Duration (seconds)', 'type' => 'number', 'required' => true, 'default' => 5],
                ],
            ],
            [
                'type' => 'action',
                'node_type' => 'sub_workflow',
                'name' => 'Execute Workflow',
                'description' => 'Execute another workflow',
                'icon' => 'workflow',
                'color' => '#0891b2',
                'category' => 'utility',
                'inputs' => ['main'],
                'outputs' => ['main'],
                'properties' => [
                    ['name' => 'workflow_id', 'displayName' => 'Workflow', 'type' => 'workflow', 'required' => true],
                    ['name' => 'wait', 'displayName' => 'Wait for Completion', 'type' => 'boolean', 'default' => true],
                ],
            ],

            // ========== START NODE (Internal) ==========
            [
                'type' => 'start',
                'node_type' => 'start',
                'name' => 'Start',
                'description' => 'Workflow start point',
                'icon' => 'play-circle',
                'color' => '#22c55e',
                'category' => 'internal',
                'inputs' => [],
                'outputs' => ['main'],
                'properties' => [],
            ],
        ];

        foreach ($nodeTypes as $nodeType) {
            NodeType::updateOrCreate(
                ['node_type' => $nodeType['node_type']],
                $nodeType
            );
        }

        $this->command->info('✅ Seeded ' . count($nodeTypes) . ' node types.');
    }
}
