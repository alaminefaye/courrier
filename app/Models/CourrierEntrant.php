<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CourrierEntrant extends Model
{
    protected $table = 'courriers_entrants';

    protected $fillable = [
        'nim',
        'provenance',
        'destinataire_service_id',
        'destinataire_user_id',
        'type_courrier',
        'personne_apporteur',
        'date_arrivee',
        'qr_code',
        'qr_code_hash',
        'statut',
        'niveau_confidentialite',
        'fichier_joint',
        'observations',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_arrivee' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function destinataireService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'destinataire_service_id');
    }

    public function destinataireUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeline(): MorphMany
    {
        return $this->morphMany(CourrierTimeline::class, 'courrier');
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(CourrierReception::class);
    }
}
