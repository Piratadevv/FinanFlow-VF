<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|string',
            'category' => 'required|string',
            'severity' => 'nullable|string',
            'message' => 'required_without:description|nullable|string',
            'description' => 'required_without:message|nullable|string',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|string',
            'user_id' => 'nullable|string',
            'changes' => 'nullable|array',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'L\'action est obligatoire.',
            'category.required' => 'La catégorie est obligatoire.',
            'message.required_without' => 'Le message ou la description est obligatoire.',
            'description.required_without' => 'La description ou le message est obligatoire.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], 422)
            );
        }
        parent::failedValidation($validator);
    }
}
