<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'required|array',
            'connections' => 'required|array',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'folder_id' => 'nullable|string|exists:folders,id',
            'trigger_config' => 'nullable|array',
            'cron_expression' => 'nullable|string',
            'timezone' => 'nullable|string',
        ];
    }
}
