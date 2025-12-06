<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'node_type',
        'name',
        'description',
        'icon',
        'color',
        'category',
        'inputs',
        'outputs',
        'properties',
        'is_active',
        'organization_id',
        'created_by',
        'is_custom',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'outputs' => 'array',
            'properties' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope: Get only active node types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get node type by node_type identifier
     */
    public static function findByNodeType(string $nodeType): ?self
    {
        return static::where('node_type', $nodeType)->first();
    }
}
