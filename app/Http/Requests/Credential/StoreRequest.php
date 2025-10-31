<?php

namespace App\Http\Requests\Credential;

use App\Enums\CredentialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => ['required', new Enum(CredentialType::class)],
            'data' => 'required|array',
        ];
    }
}
