<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Refinancement extends Model
{
    use HasUuids;

    protected $fillable = [
        'libelle',
        'montant_refinance',
        'taux_interet',
        'date_refinancement',
        'duree_en_mois',
        'encours_refinance',
        'frais_dossier',
        'conditions',
        'statut',
        'total_interets',
        'ordre_saisie',
    ];

    protected $casts = [
        'date_refinancement' => 'date',
        'montant_refinance' => 'decimal:2',
        'taux_interet' => 'decimal:2',
        'encours_refinance' => 'decimal:2',
        'frais_dossier' => 'decimal:2',
        'total_interets' => 'decimal:2',
        'duree_en_mois' => 'integer',
        'ordre_saisie' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Refinancement $refinancement) {
            $refinancement->total_interets = self::calculateInterets(
                $refinancement->montant_refinance,
                $refinancement->taux_interet,
                $refinancement->duree_en_mois
            );

            if (!$refinancement->ordre_saisie) {
                $refinancement->ordre_saisie = static::count() + 1;
            }
        });

        static::updating(function (Refinancement $refinancement) {
            if (
                $refinancement->isDirty('montant_refinance') ||
                $refinancement->isDirty('taux_interet') ||
                $refinancement->isDirty('duree_en_mois')
            ) {
                $refinancement->total_interets = self::calculateInterets(
                    $refinancement->montant_refinance,
                    $refinancement->taux_interet,
                    $refinancement->duree_en_mois
                );
            }
        });
    }

    public static function calculateInterets(float $montant, float $taux, int $duree): float
    {
        return round($montant * ($taux / 100) * ($duree / 12), 2);
    }
}
