# 📱 Documentation API Mobile

## Base URL
```
/api/v1
```

## Authentification

L'API utilise Laravel Sanctum pour l'authentification. Vous devez obtenir un token d'authentification.

### Obtenir un token
```http
POST /api/login
Content-Type: application/json

{
    "email": "agent1@admin.com",
    "password": "password"
}
```

**Réponse:**
```json
{
    "token": "1|xxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "Agent Courrier 1",
        "email": "agent1@admin.com"
    }
}
```

### Utiliser le token
Ajoutez le header suivant à toutes les requêtes authentifiées :
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Scanner un QR Code (Public)
```http
POST /api/v1/qrcode/scan
Content-Type: application/json

{
    "qr_data": "{\"nim\":\"ARR-2025-00001\",\"type\":\"entrant\",...}"
}
```

**Réponse:**
```json
{
    "success": true,
    "authenticite_verifiee": true,
    "courrier": {
        "id": 1,
        "nim": "ARR-2025-00001",
        "type": "entrant",
        "provenance": "Ministère de l'Intérieur",
        "destinataire": "Service RH",
        "statut": "transmis",
        "date": "2025-11-27 10:30:00",
        "confidentialite": "urgent"
    }
}
```

### 2. Vérifier un QR Code (Public)
```http
GET /api/v1/qrcode/verify?qr_data={qr_data}&hash={hash}
```

### 3. Récupérer un courrier par NIM (Authentifié)
```http
GET /api/v1/courrier/{nim}
Authorization: Bearer {token}
```

**Réponse:**
```json
{
    "success": true,
    "type": "entrant",
    "courrier": {
        "id": 1,
        "nim": "ARR-2025-00001",
        "provenance": "Ministère de l'Intérieur",
        "destinataire_service": "Service RH",
        "type_courrier": "urgent",
        "niveau_confidentialite": "urgent",
        "statut": "transmis",
        "date_arrivee": "2025-11-27 10:30:00",
        "personne_apporteur": "M. Diallo",
        "observations": "..."
    }
}
```

### 4. Confirmer réception courrier entrant (Authentifié)
```http
POST /api/v1/courrier/{nim}/confirmer-reception
Authorization: Bearer {token}
Content-Type: application/json

{
    "signature_type": "qr_scan",
    "commentaire": "Courrier reçu en bon état"
}
```

**Réponse:**
```json
{
    "success": true,
    "message": "Réception confirmée avec succès",
    "date_reception": "2025-11-27 14:30:00"
}
```

### 5. Confirmer livraison courrier sortant (Authentifié)
```http
POST /api/v1/courrier/{nim}/confirmer-livraison
Authorization: Bearer {token}
Content-Type: application/json

{
    "signature_type": "qr_scan",
    "commentaire": "Livré au destinataire"
}
```

### 6. Liste des courriers (Authentifié)
```http
GET /api/v1/courriers?type=entrants&limit=20
Authorization: Bearer {token}
```

**Paramètres:**
- `type`: `entrants`, `sortants`, ou `tous` (défaut: `entrants`)
- `limit`: Nombre de résultats (défaut: 20)

**Réponse:**
```json
{
    "success": true,
    "count": 5,
    "courriers": [
        {
            "id": 1,
            "nim": "ARR-2025-00001",
            "type": "entrant",
            "provenance": "Ministère",
            "destinataire": "Service RH",
            "statut": "transmis",
            "date": "2025-11-27 10:30:00",
            "niveau_confidentialite": "urgent"
        }
    ]
}
```

---

## Codes de réponse

- `200` - Succès
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Accès refusé
- `404` - Ressource non trouvée
- `500` - Erreur serveur

---

## Exemples d'utilisation

### Scanner et confirmer réception
```javascript
// 1. Scanner le QR Code
const scanResponse = await fetch('/api/v1/qrcode/scan', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ qr_data: scannedData })
});

const scanData = await scanResponse.json();

// 2. Confirmer la réception
if (scanData.success && scanData.courrier.type === 'entrant') {
    await fetch(`/api/v1/courrier/${scanData.courrier.nim}/confirmer-reception`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            signature_type: 'qr_scan',
            commentaire: 'Reçu via application mobile'
        })
    });
}
```

