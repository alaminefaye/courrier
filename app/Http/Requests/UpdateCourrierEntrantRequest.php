<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourrierEntrantRequest extends FormRequest
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
            'provenance' => 'sometimes|required|string|max:255',
            'destinataire_service_id' => 'sometimes|required|exists:services,id',
            'destinataire_user_id' => 'nullable|exists:users,id',
            'type_courrier' => 'sometimes|required|in:ordinaire,urgent,confidentiel,secret_defense',
            'personne_apporteur' => 'sometimes|required|string|max:255',
            'fichier_joint' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'observations' => 'nullable|string',
            'niveau_confidentialite' => 'sometimes|required|in:ordinaire,urgent,confidentiel,secret_defense',
        ];
    }
}
