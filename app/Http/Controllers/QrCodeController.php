<?php

namespace App\Http\Controllers;

use App\Models\CourrierEntrant;
use App\Models\CourrierSortant;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Scanner un QR Code et afficher les informations
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        $qrData = $request->qr_data;
        $data = json_decode($qrData, true);

        if (!$data || !isset($data['nim']) || !isset($data['hash'])) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code invalide'
            ], 400);
        }

        // Vérifier l'authenticité
        $isValid = $this->qrCodeService->verifyQrCode($qrData, $data['hash']);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code falsifié ou corrompu'
            ], 400);
        }

        // Récupérer le courrier
        $courrier = null;
        if ($data['type'] === 'entrant') {
            $courrier = CourrierEntrant::where('nim', $data['nim'])->first();
        } else {
            $courrier = CourrierSortant::where('nim', $data['nim'])->first();
        }

        if (!$courrier) {
            return response()->json([
                'success' => false,
                'message' => 'Courrier non trouvé'
            ], 404);
        }

        // Préparer les données du courrier
        $courrierData = [
            'id' => $courrier->id,
            'nim' => $courrier->nim,
            'type' => $data['type'],
            'statut' => $courrier->statut,
            'date' => $data['date'],
            'confidentialite' => $courrier->niveau_confidentialite,
        ];

        if ($courrier instanceof CourrierEntrant) {
            $courrierData['provenance'] = $courrier->provenance;
            $courrierData['destinataire'] = $courrier->destinataireService->nom ?? null;
        } else {
            $courrierData['destinataire'] = $courrier->destinataire_externe;
            $courrierData['provenance'] = $courrier->provenanceService->nom ?? null;
        }

        return response()->json([
            'success' => true,
            'authenticite_verifiee' => true,
            'courrier' => $courrierData,
            'details' => $data
        ]);
    }

    /**
     * Vérifier l'authenticité d'un QR Code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'hash' => 'required|string',
        ]);

        $isValid = $this->qrCodeService->verifyQrCode($request->qr_data, $request->hash);

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'QR Code authentique' : 'QR Code invalide'
        ]);
    }
}
