<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourrierSortantRequest extends FormRequest
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
            'destinataire_externe' => 'required|string|max:255',
            'provenance_service_id' => 'required|exists:services,id',
            'provenance_user_id' => 'nullable|exists:users,id',
            'type_courrier' => 'required|in:ordinaire,urgent,confidentiel,secret_defense',
            'personne_livreur' => 'required|string|max:255',
            'fichier_joint' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'observations' => 'nullable|string',
            'niveau_confidentialite' => 'required|in:ordinaire,urgent,confidentiel,secret_defense',
        ];
    }

    public function messages(): array
    {
        return [
            'destinataire_externe.required' => 'Le champ destinataire externe est obligatoire.',
            'provenance_service_id.required' => 'Le service d\'origine est obligatoire.',
            'provenance_service_id.exists' => 'Le service sélectionné n\'existe pas.',
            'type_courrier.required' => 'Le type de courrier est obligatoire.',
            'personne_livreur.required' => 'Le nom de la personne qui livre le courrier est obligatoire.',
            'fichier_joint.max' => 'Le fichier ne doit pas dépasser 10MB.',
            'fichier_joint.mimes' => 'Le fichier doit être de type : pdf, jpg, jpeg, png, doc, docx.',
        ];
    }
}
