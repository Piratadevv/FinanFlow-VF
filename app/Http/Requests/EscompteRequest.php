<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EscompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_remise' => 'required|date|before_or_equal:tomorrow|after_or_equal:' . now()->subYears(5)->format('Y-m-d'),
            'libelle' => 'required|string|min:1|max:255',
            'montant' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'numero_effet' => 'nullable|string|max:50',
            'nom_tireur' => 'nullable|string|max:255',
            'taux_escompte' => 'nullable|numeric|min:0|max:100',
            'frais_commission' => 'nullable|numeric|min:0',
            'montant_net' => 'nullable|numeric|min:0',
            'statut' => 'sometimes|in:ACTIF,TERMINE,SUSPENDU',
        ];
    }

    public function messages(): array
    {
        return [
            'date_remise.required' => 'La date de remise est obligatoire.',
            'date_remise.date' => 'La date de remise doit être une date valide.',
            'date_remise.before_or_equal' => 'La date de remise ne peut pas être dans le futur.',
            'date_remise.after_or_equal' => 'La date de remise ne peut pas être antérieure à 5 ans.',
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.gt' => 'Le montant doit être positif.',
            'montant.regex' => 'Le montant doit avoir au maximum 2 décimales.',
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
