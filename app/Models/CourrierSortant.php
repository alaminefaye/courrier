<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CourrierSortant extends Model
{
    protected $table = 'courriers_sortants';

    protected $fillable = [
        'nim',
        'destinataire_externe',
        'provenance_service_id',
        'provenance_user_id',
        'type_courrier',
        'personne_livreur',
        'date_depart',
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
            'date_depart' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function provenanceService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'provenance_service_id');
    }

    public function provenanceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provenance_user_id');
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
