<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'autorisation_bancaire' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'autorisation_bancaire.required' => 'L\'autorisation bancaire est obligatoire.',
            'autorisation_bancaire.numeric' => 'L\'autorisation bancaire doit être un nombre.',
            'autorisation_bancaire.gt' => 'L\'autorisation bancaire doit être positive.',
            'autorisation_bancaire.regex' => 'L\'autorisation bancaire doit avoir au maximum 2 décimales.',
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
