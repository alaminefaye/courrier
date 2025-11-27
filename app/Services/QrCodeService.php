<?php

namespace App\Services;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Génère les données JSON pour le QR Code
     */
    public function generateQrData($courrier): array
    {
        $data = [
            'nim' => $courrier->nim,
            'type' => $courrier instanceof CourrierEntrant ? 'entrant' : 'sortant',
            'date' => $courrier instanceof CourrierEntrant 
                ? $courrier->date_arrivee->format('Y-m-d H:i:s')
                : $courrier->date_depart->format('Y-m-d H:i:s'),
            'confidentialite' => $courrier->niveau_confidentialite,
        ];

        if ($courrier instanceof CourrierEntrant) {
            $data['expediteur'] = $courrier->provenance;
            $data['destinataire'] = $courrier->destinataireService->nom ?? '';
        } else {
            $data['destinataire'] = $courrier->destinataire_externe;
            $data['expediteur'] = $courrier->provenanceService->nom ?? '';
        }

        return $data;
    }

    /**
     * Génère le hash HMAC pour vérification
     */
    public function generateHash(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $secret = config('app.key');
        
        return hash_hmac('sha256', $json, $secret);
    }

    /**
     * Génère l'image QR Code
     */
    public function generateQrCodeImage($courrier): string
    {
        $data = $this->generateQrData($courrier);
        $hash = $this->generateHash($data);
        
        $data['hash'] = $hash;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Générer l'image QR Code
        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate($json);
        
        return $qrCode;
    }

    /**
     * Vérifie l'authenticité du QR Code
     */
    public function verifyQrCode(string $qrData, string $hash): bool
    {
        $data = json_decode($qrData, true);
        
        if (!$data || !isset($data['hash'])) {
            return false;
        }
        
        // Retirer le hash pour recalculer
        $originalHash = $data['hash'];
        unset($data['hash']);
        
        $calculatedHash = $this->generateHash($data);
        
        return hash_equals($originalHash, $calculatedHash);
    }

    /**
     * Génère le QR Code et le hash pour un courrier
     */
    public function generateForCourrier($courrier): array
    {
        $data = $this->generateQrData($courrier);
        $hash = $this->generateHash($data);
        
        $data['hash'] = $hash;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        return [
            'qr_code' => $json,
            'qr_code_hash' => $hash,
        ];
    }
}

