<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreVariableRequest extends FormRequest
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
            'type' => 'sometimes|string|in:string,number,boolean,json,encrypted',
            'description' => 'nullable|string',
            'is_secret' => 'sometimes|boolean',
            'workflow_id' => 'nullable|integer|exists:workflows,id',
        ];
    }
}
