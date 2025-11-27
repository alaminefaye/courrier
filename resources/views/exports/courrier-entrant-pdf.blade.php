<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fiche Courrier Entrant - {{ $courrier->nim }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 30%; background-color: #f5f5f5; }
        .qr-code { text-align: center; margin: 20px 0; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FICHE COURRIER ENTRANT</h1>
        <p><strong>NIM: {{ $courrier->nim }}</strong></p>
    </div>

    <table class="info-table">
        <tr>
            <td>Provenance</td>
            <td>{{ $courrier->provenance }}</td>
        </tr>
        <tr>
            <td>Service Destinataire</td>
            <td>{{ $courrier->destinataireService->nom ?? '-' }}</td>
        </tr>
        <tr>
            <td>Type de Courrier</td>
            <td>{{ config('courrier.types_courrier')[$courrier->type_courrier] ?? $courrier->type_courrier }}</td>
        </tr>
        <tr>
            <td>Niveau de Confidentialité</td>
            <td>{{ config('courrier.niveaux_confidentialite')[$courrier->niveau_confidentialite] ?? $courrier->niveau_confidentialite }}</td>
        </tr>
        <tr>
            <td>Date d'Arrivée</td>
            <td>{{ $courrier->date_arrivee->format('d/m/Y à H:i') }}</td>
        </tr>
        <tr>
            <td>Personne Apporteur</td>
            <td>{{ $courrier->personne_apporteur }}</td>
        </tr>
        <tr>
            <td>Statut</td>
            <td>{{ config('courrier.statuts_entrant')[$courrier->statut] ?? $courrier->statut }}</td>
        </tr>
        @if($courrier->observations)
        <tr>
            <td>Observations</td>
            <td>{{ $courrier->observations }}</td>
        </tr>
        @endif
    </table>

    @if($courrier->qr_code)
    <div class="qr-code">
        <p><strong>QR Code d'Authentification</strong></p>
        <img src="data:image/png;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(150)->generate($courrier->qr_code)) }}" alt="QR Code">
        <p style="font-size: 10px;">Scannez ce QR Code pour vérifier l'authenticité</p>
    </div>
    @endif

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
        <p>Système de Gestion de Courrier</p>
    </div>
</body>
</html>

