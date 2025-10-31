<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workflow_id' => 'sometimes|string|exists:workflows,id',
            'node_id' => 'sometimes|string',
            'method' => 'sometimes|string|in:get,post,put,patch,delete',
            'path' => 'sometimes|string|max:255',
            'auth_type' => 'nullable|string',
            'auth_config' => 'nullable|array',
            'ip_whitelist' => 'nullable|array',
            'active' => 'sometimes|boolean',
        ];
    }
}
