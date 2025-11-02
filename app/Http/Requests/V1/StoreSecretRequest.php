<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSecretRequest extends FormRequest
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
            'key' => 'required|string|max:255',
            'value' => 'required|string',
            'description' => 'nullable|string',
            'workflow_id' => 'nullable|integer|exists:workflows,id',
        ];
    }
}
