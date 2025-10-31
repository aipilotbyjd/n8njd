<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
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
            'workflow_definition' => 'required|array',
            'tags' => 'nullable|array',
            'category' => 'nullable|string',
            'is_public' => 'sometimes|boolean',
        ];
    }
}
