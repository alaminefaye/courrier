<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourrierReception extends Model
{
    protected $table = 'courrier_receptions';

    protected $fillable = [
        'courrier_entrant_id',
        'courrier_sortant_id',
        'user_id',
        'signature_type',
        'signature_data',
        'date_reception',
        'ip_address',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'date_reception' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function courrierEntrant(): BelongsTo
    {
        return $this->belongsTo(CourrierEntrant::class);
    }

    public function courrierSortant(): BelongsTo
    {
        return $this->belongsTo(CourrierSortant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
