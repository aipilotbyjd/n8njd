<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'value' => 'sometimes|string',
            'is_secret' => 'sometimes|boolean',
            'scope' => 'sometimes|string|in:global,environment,workflow',
        ];
    }
}
