<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'sometimes|array',
            'connections' => 'sometimes|array',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'folder_id' => 'nullable|string|exists:folders,id',
            'trigger_config' => 'nullable|array',
            'cron_expression' => 'nullable|string',
            'timezone' => 'nullable|string',
        ];
    }
}
