<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'nom',
        'code',
        'description',
        'direction_id',
        'responsable_id',
    ];

    /**
     * Relations
     */
    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function courriersEntrants(): HasMany
    {
        return $this->hasMany(CourrierEntrant::class, 'destinataire_service_id');
    }

    public function courriersSortants(): HasMany
    {
        return $this->hasMany(CourrierSortant::class, 'provenance_service_id');
    }
}
