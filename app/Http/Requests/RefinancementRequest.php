<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RefinancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_refinancement' => 'required|date|before_or_equal:tomorrow',
            'libelle' => 'required|string|min:1|max:255',
            'montant_refinance' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'taux_interet' => 'required|numeric|min:0|max:100',
            'duree_en_mois' => 'required|integer|min:1|max:360',
            'encours_refinance' => 'required|numeric|min:0',
            'frais_dossier' => 'nullable|numeric|min:0',
            'conditions' => 'nullable|string|max:500',
            'statut' => 'required|in:ACTIF,TERMINE,SUSPENDU',
        ];
    }

    public function messages(): array
    {
        return [
            'date_refinancement.required' => 'La date de refinancement est obligatoire.',
            'date_refinancement.date' => 'La date de refinancement doit être une date valide.',
            'date_refinancement.before_or_equal' => 'La date de refinancement ne peut pas être dans le futur.',
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'montant_refinance.required' => 'Le montant refinancé est obligatoire.',
            'montant_refinance.gt' => 'Le montant refinancé doit être positif.',
            'montant_refinance.regex' => 'Le montant doit avoir au maximum 2 décimales.',
            'taux_interet.required' => 'Le taux d\'intérêt est obligatoire.',
            'taux_interet.min' => 'Le taux ne peut pas être négatif.',
            'taux_interet.max' => 'Le taux ne peut pas dépasser 100%.',
            'duree_en_mois.required' => 'La durée est obligatoire.',
            'duree_en_mois.min' => 'La durée doit être d\'au moins 1 mois.',
            'duree_en_mois.max' => 'La durée ne peut pas dépasser 360 mois.',
            'encours_refinance.required' => 'L\'encours refinancé est obligatoire.',
            'encours_refinance.min' => 'L\'encours refinancé ne peut pas être négatif.',
            'conditions.max' => 'Les conditions ne peuvent pas dépasser 500 caractères.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut doit être ACTIF, TERMINE ou SUSPENDU.',
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
