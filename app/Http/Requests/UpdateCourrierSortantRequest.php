<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourrierSortantRequest extends FormRequest
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
            'destinataire_externe' => 'sometimes|required|string|max:255',
            'provenance_service_id' => 'sometimes|required|exists:services,id',
            'provenance_user_id' => 'nullable|exists:users,id',
            'type_courrier' => 'sometimes|required|in:ordinaire,urgent,confidentiel,secret_defense',
            'personne_livreur' => 'sometimes|required|string|max:255',
            'fichier_joint' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'observations' => 'nullable|string',
            'niveau_confidentialite' => 'sometimes|required|in:ordinaire,urgent,confidentiel,secret_defense',
        ];
    }
}
