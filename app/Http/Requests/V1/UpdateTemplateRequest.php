<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
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
            'workflow_definition' => 'sometimes|array',
            'tags' => 'nullable|array',
            'category' => 'nullable|string',
            'is_public' => 'sometimes|boolean',
        ];
    }
}
