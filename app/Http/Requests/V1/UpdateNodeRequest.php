<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'properties' => 'nullable|array',
            'is_trigger' => 'sometimes|boolean',
        ];
    }
}
