<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $courrier->nim }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #fff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 2px solid #e0e0e0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #696cff;
        }
        .header h1 {
            color: #696cff;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header .nim {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .qr-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .qr-code {
            display: inline-block;
            padding: 20px;
            background: white;
            border: 2px solid #696cff;
            border-radius: 8px;
            margin: 20px 0;
        }
        .qr-code img {
            max-width: 300px;
            height: auto;
        }
        .info-section {
            margin-top: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            width: 40%;
        }
        .info-value {
            color: #333;
            width: 60%;
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 12px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            text-align: center;
            color: #856404;
        }
        @media print {
            body {
                padding: 0;
            }
            .container {
                border: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>QR CODE D'AUTHENTIFICATION</h1>
            <div class="nim">NIM: {{ $courrier->nim }}</div>
        </div>

        <div class="qr-section">
            <h3 style="margin-bottom: 20px; color: #333;">Scannez ce code pour vérifier l'authenticité</h3>
            
            @if($courrier->qr_code)
                <div class="qr-code">
                    @php
                        $qrCodeImage = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                            ->size(300)
                            ->generate($courrier->qr_code);
                        $qrCodeBase64 = base64_encode($qrCodeImage);
                    @endphp
                    <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code {{ $courrier->nim }}">
                </div>
            @else
                <div class="warning">
                    ⚠️ QR Code non généré pour ce courrier
                </div>
            @endif

            <p style="margin-top: 20px; color: #666; font-size: 14px;">
                Ce QR Code contient toutes les informations nécessaires pour vérifier l'authenticité de ce courrier.
            </p>
        </div>

        <div class="info-section">
            @if($courrier instanceof \App\Models\CourrierEntrant)
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">Courrier Entrant</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Provenance:</span>
                    <span class="info-value">{{ $courrier->provenance }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Service Destinataire:</span>
                    <span class="info-value">{{ $courrier->destinataireService->nom ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date d'Arrivée:</span>
                    <span class="info-value">{{ $courrier->date_arrivee->format('d/m/Y à H:i') }}</span>
                </div>
            @else
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">Courrier Sortant</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Destinataire:</span>
                    <span class="info-value">{{ $courrier->destinataire_externe }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Service d'Origine:</span>
                    <span class="info-value">{{ $courrier->provenanceService->nom ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date de Départ:</span>
                    <span class="info-value">{{ $courrier->date_depart->format('d/m/Y à H:i') }}</span>
                </div>
            @endif

            <div class="info-row">
                <span class="info-label">Niveau de Confidentialité:</span>
                <span class="info-value">{{ config('courrier.niveaux_confidentialite')[$courrier->niveau_confidentialite] ?? $courrier->niveau_confidentialite }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">Statut:</span>
                <span class="info-value">
                    @if($courrier instanceof \App\Models\CourrierEntrant)
                        {{ config('courrier.statuts_entrant')[$courrier->statut] ?? $courrier->statut }}
                    @else
                        {{ config('courrier.statuts_sortant')[$courrier->statut] ?? $courrier->statut }}
                    @endif
                </span>
            </div>
        </div>

        <div class="footer">
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Système de Gestion de Courrier - Version 1.0</p>
            <p style="margin-top: 10px; font-size: 11px;">
                Ce document est confidentiel. Ne pas partager sans autorisation.
            </p>
        </div>
    </div>
</body>
</html>

