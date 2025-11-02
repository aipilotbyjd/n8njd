<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVariableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('name') && !$this->has('key')) {
            $this->merge(['key' => $this->input('name')]);
        }
    }

    public function rules(): array
    {
        return [
            'key' => 'sometimes|string|max:255',
            'value' => 'sometimes|string',
            'type' => 'sometimes|string|in:string,number,boolean,json,encrypted',
            'description' => 'nullable|string',
            'is_secret' => 'sometimes|boolean',
            'workflow_id' => 'nullable|integer|exists:workflows,id',
        ];
    }
}
