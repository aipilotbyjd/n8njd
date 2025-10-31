<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workflow_id' => 'required|string|exists:workflows,id',
            'node_id' => 'required|string',
            'method' => 'required|string|in:get,post,put,patch,delete',
            'path' => 'required|string|max:255',
            'auth_type' => 'nullable|string',
            'auth_config' => 'nullable|array',
            'ip_whitelist' => 'nullable|array',
            'active' => 'sometimes|boolean',
        ];
    }
}
