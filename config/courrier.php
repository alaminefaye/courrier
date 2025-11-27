<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration du système de gestion de courrier
    |--------------------------------------------------------------------------
    */

    'archive' => [
        'jours_avant_archivage' => env('COURRIER_JOURS_ARCHIVAGE', 90),
        'dossier_archive' => 'archives',
    ],

    'qr_code' => [
        'size' => env('QR_CODE_SIZE', 300),
        'format' => env('QR_CODE_FORMAT', 'png'),
    ],

    'niveaux_confidentialite' => [
        'ordinaire' => 'Ordinaire',
        'urgent' => 'Urgent',
        'confidentiel' => 'Confidentiel',
        'secret_defense' => 'Secret Défense',
    ],

    'types_courrier' => [
        'ordinaire' => 'Ordinaire',
        'urgent' => 'Urgent',
        'confidentiel' => 'Confidentiel',
        'secret_defense' => 'Secret Défense',
    ],

    'statuts_entrant' => [
        'enregistre' => 'Enregistré',
        'transmis' => 'Transmis',
        'recu' => 'Reçu',
        'en_retard' => 'En retard',
        'non_retire' => 'Non retiré',
    ],

    'statuts_sortant' => [
        'enregistre' => 'Enregistré',
        'transmis' => 'Transmis',
        'livre' => 'Livré',
        'confirme' => 'Confirmé',
    ],

    'roles' => [
        'admin' => 'Administrateur',
        'agent_courrier' => 'Agent Courrier',
        'chef_service' => 'Chef de Service',
        'directeur' => 'Directeur',
    ],

    'fichiers' => [
        'max_size' => 10240, // 10MB en KB
        'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
        'encrypt_levels' => ['confidentiel', 'secret_defense'],
    ],
];

