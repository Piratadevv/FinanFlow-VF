<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Escompte extends Model
{
    use HasUuids;

    protected $fillable = [
        'numero_effet',
        'nom_tireur',
        'date_remise',
        'libelle',
        'montant',
        'taux_escompte',
        'frais_commission',
        'montant_net',
        'statut',
        'ordre_saisie',
    ];

    protected $casts = [
        'date_remise' => 'date',
        'montant' => 'decimal:2',
        'taux_escompte' => 'decimal:2',
        'frais_commission' => 'decimal:2',
        'montant_net' => 'decimal:2',
        'ordre_saisie' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Escompte $escompte) {
            if (!$escompte->ordre_saisie) {
                $escompte->ordre_saisie = static::count() + 1;
            }
            // Auto-generate numero_effet if not provided
            if (empty($escompte->numero_effet)) {
                $last = static::where('numero_effet', 'LIKE', 'EFF%')
                    ->orderByDesc('numero_effet')
                    ->value('numero_effet');
                $nextNum = $last ? ((int)substr($last, 3)) + 1 : 1;
                $escompte->numero_effet = 'EFF' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
