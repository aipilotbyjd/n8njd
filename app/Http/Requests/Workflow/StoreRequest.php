<?php

namespace App\Http\Requests\Workflow;

use App\Enums\WorkflowStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Replace with your authorization logic
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', new Enum(WorkflowStatus::class)],
            'nodes' => 'required|array',
            'connections' => 'required|array',
        ];
    }
}
