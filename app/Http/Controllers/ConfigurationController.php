<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Escompte;
use App\Models\Refinancement;
use App\Http\Requests\ConfigurationRequest;
use App\Services\AuditLogger;
use Database\Seeders\EscompteSeeder;
use Illuminate\Http\JsonResponse;

class ConfigurationController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(Configuration::current());
    }

    public function update(ConfigurationRequest $request): JsonResponse
    {
        $config = Configuration::current();
        $before = $config->toArray();

        $config->update($request->validated());
        $config->refresh();

        AuditLogger::log(
            'UPDATE',
            'configuration',
            'info',
            'Modification de la configuration',
            'Autorisation bancaire mise à jour à ' . number_format($config->autorisation_bancaire, 2, ',', ' ') . ' DH',
            'configuration',
            '1',
            ['before' => $before, 'after' => $config->toArray()]
        );

        return response()->json($config);
    }

    public function reset(): JsonResponse
    {
        // Reset autorisation to 100000
        $config = Configuration::current();
        $config->update(['autorisation_bancaire' => 100000]);

        // Truncate escomptes and re-seed
        Escompte::truncate();
        (new EscompteSeeder())->run();

        AuditLogger::log(
            'UPDATE',
            'configuration',
            'MEDIUM',
            'Réinitialisation de la configuration',
            'Configuration réinitialisée: autorisation = 100 000 DH, escomptes restaurés'
        );

        // Return updated KPIs
        $cumulTotal = (float) Escompte::sum('montant');
        $cumulRefinancements = (float) Refinancement::sum('montant_refinance');
        $cumulGlobal = $cumulTotal + $cumulRefinancements;
        $autorisation = 100000.0;

        return response()->json([
            'configuration' => $config->fresh(),
            'kpi' => [
                'cumulTotal' => $cumulTotal,
                'encoursRestant' => $autorisation - $cumulTotal,
                'nombreEscomptes' => Escompte::count(),
                'pourcentageUtilisation' => $autorisation > 0 ? round(($cumulTotal / $autorisation) * 100) : 0,
                'cumulRefinancements' => $cumulRefinancements,
                'nombreRefinancements' => Refinancement::count(),
                'cumulGlobal' => $cumulGlobal,
                'encoursRestantGlobal' => $autorisation - $cumulGlobal,
                'pourcentageUtilisationGlobal' => $autorisation > 0 ? round(($cumulGlobal / $autorisation) * 100) : 0,
                'autorisationBancaire' => $autorisation,
            ],
        ]);
    }

    public function validateAutorisation(): JsonResponse
    {
        // Stub — always returns valid
        return response()->json(['valid' => true]);
    }

    public function calculateImpact(): JsonResponse
    {
        // Stub with hardcoded values
        return response()->json([
            'impact' => [
                'newEncours' => 0,
                'newUtilisation' => 0,
                'difference' => 0,
            ],
        ]);
    }
}
