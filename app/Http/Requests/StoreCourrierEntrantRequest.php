<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourrierEntrantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provenance' => 'required|string|max:255',
            'destinataire_service_id' => 'required|exists:services,id',
            'destinataire_user_id' => 'nullable|exists:users,id',
            'type_courrier' => 'required|in:ordinaire,urgent,confidentiel,secret_defense',
            'personne_apporteur' => 'required|string|max:255',
            'fichier_joint' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'observations' => 'nullable|string',
            'niveau_confidentialite' => 'required|in:ordinaire,urgent,confidentiel,secret_defense',
        ];
    }

    public function messages(): array
    {
        return [
            'provenance.required' => 'Le champ provenance est obligatoire.',
            'destinataire_service_id.required' => 'Le service destinataire est obligatoire.',
            'destinataire_service_id.exists' => 'Le service sélectionné n\'existe pas.',
            'type_courrier.required' => 'Le type de courrier est obligatoire.',
            'personne_apporteur.required' => 'Le nom de la personne qui apporte le courrier est obligatoire.',
            'fichier_joint.max' => 'Le fichier ne doit pas dépasser 10MB.',
            'fichier_joint.mimes' => 'Le fichier doit être de type : pdf, jpg, jpeg, png, doc, docx.',
        ];
    }
}
