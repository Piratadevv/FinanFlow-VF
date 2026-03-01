<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Escompte;
use App\Models\Log;
use App\Models\Refinancement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function kpi(): JsonResponse
    {
        $cumulTotal = (float) Escompte::sum('montant');
        $nombreEscomptes = Escompte::count();
        $cumulRefinancements = (float) Refinancement::sum('montant_refinance');
        $nombreRefinancements = Refinancement::count();
        $cumulGlobal = $cumulTotal + $cumulRefinancements;
        $autorisation = (float) Configuration::current()->autorisation_bancaire;
        $encoursRestant = $autorisation - $cumulTotal;
        $encoursRestantGlobal = $autorisation - $cumulGlobal;
        $pourcentageUtilisation = $autorisation > 0 ? round(($cumulTotal / $autorisation) * 100) : 0;
        $pourcentageUtilisationGlobal = $autorisation > 0 ? round(($cumulGlobal / $autorisation) * 100) : 0;
        return response()->json([
            'cumulTotal' => $cumulTotal,
            'encoursRestant' => $encoursRestant,
            'nombreEscomptes' => $nombreEscomptes,
            'pourcentageUtilisation' => $pourcentageUtilisation,
            'cumulRefinancements' => $cumulRefinancements,
            'nombreRefinancements' => $nombreRefinancements,
            'cumulGlobal' => $cumulGlobal,
            'encoursRestantGlobal' => $encoursRestantGlobal,
            'pourcentageUtilisationGlobal' => $pourcentageUtilisationGlobal,
            'autorisationBancaire' => $autorisation,
        ]);
    }

    public function analytics()
    {
        $config = Configuration::current();
        $autorisation = (float) $config->autorisation_bancaire;
        $escomptes = Escompte::all();
        $esc_count = $escomptes->count();
        $esc_cumul = (float) $escomptes->sum('montant');
        $esc_by_statut = [
            'ACTIF' => $escomptes->where('statut', 'ACTIF')->count(),
            'TERMINE' => $escomptes->where('statut', 'TERMINE')->count(),
            'SUSPENDU' => $escomptes->where('statut', 'SUSPENDU')->count(),
        ];
        $esc_avg = $esc_count > 0 ? round($esc_cumul / $esc_count, 2) : 0;
        $esc_min = $esc_count > 0 ? (float) $escomptes->min('montant') : 0;
        $esc_max = $esc_count > 0 ? (float) $escomptes->max('montant') : 0;
        $esc_top5 = $escomptes->sortByDesc('montant')->take(5)->map(fn($e) => [
            'libelle' => $e->libelle,
            'montant' => (float) $e->montant,
        ])->values()->toArray();
        $esc_monthly = $this->monthlyTotals(Escompte::class, 'montant', 'date_remise');

        $refis = Refinancement::all();
        $ref_count = $refis->count();
        $ref_cumul = (float) $refis->sum('montant_refinance');
        $ref_interets = (float) $refis->sum('total_interets');
        $ref_by_statut = [
            'ACTIF' => $refis->where('statut', 'ACTIF')->count(),
            'TERMINE' => $refis->where('statut', 'TERMINE')->count(),
            'SUSPENDU' => $refis->where('statut', 'SUSPENDU')->count(),
        ];
        $ref_avg_taux = $ref_count > 0 ? round((float) $refis->avg('taux_interet'), 2) : 0;
        $ref_duree = [
            'lte12' => $refis->where('duree_en_mois', '<=', 12)->count(),
            '13_24' => $refis->filter(fn($r) => $r->duree_en_mois >= 13 && $r->duree_en_mois <= 24)->count(),
            '25_36' => $refis->filter(fn($r) => $r->duree_en_mois >= 25 && $r->duree_en_mois <= 36)->count(),
            'gt36' => $refis->where('duree_en_mois', '>', 36)->count(),
        ];
        $ref_monthly = $this->monthlyTotals(Refinancement::class, 'montant_refinance', 'date_refinancement');

        $cumulGlobal = $esc_cumul + $ref_cumul;
        $encoursRestant = $autorisation - $esc_cumul;
        $encoursRestantGlobal = $autorisation - $cumulGlobal;
        $pourcentageUtilisation = $autorisation > 0 ? round(($esc_cumul / $autorisation) * 100) : 0;
        $pourcentageUtilisationGlobal = $autorisation > 0 ? round(($cumulGlobal / $autorisation) * 100) : 0;

        $logs = Log::all();
        $log_total = $logs->count();
        $now = now();
        $log_by_action = [
            'CREATE' => $logs->where('action', 'CREATE')->count(),
            'UPDATE' => $logs->where('action', 'UPDATE')->count(),
            'DELETE' => $logs->where('action', 'DELETE')->count(),
            'LOGIN' => $logs->where('action', 'LOGIN')->count(),
            'EXPORT' => $logs->where('action', 'EXPORT')->count(),
        ];
        $log_by_severity = [
            'CRITICAL' => $logs->where('severity', 'CRITICAL')->count(),
            'HIGH' => $logs->where('severity', 'HIGH')->count(),
            'MEDIUM' => $logs->where('severity', 'MEDIUM')->count(),
            'LOW' => $logs->where('severity', 'LOW')->count(),
            'info' => $logs->where('severity', 'info')->count(),
        ];
        $log_last24h = $logs->filter(fn($l) => Carbon::parse($l->timestamp)->isAfter($now->copy()->subDay()))->count();
        $log_last7d = $logs->filter(fn($l) => Carbon::parse($l->timestamp)->isAfter($now->copy()->subDays(7)))->count();
        $log_last5 = $logs->sortByDesc('timestamp')->take(5)->values()->toArray();
        $log_daily = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $label = $day->locale('fr')->isoFormat('ddd');
            $count = $logs->filter(fn($l) => Carbon::parse($l->timestamp)->isSameDay($day))->count();
            $log_daily[] = ['label' => ucfirst($label), 'count' => $count];
        }
        return view('dashboard.index', compact(
            'autorisation',
            'esc_count', 'esc_cumul', 'esc_by_statut', 'esc_avg', 'esc_min', 'esc_max', 'esc_top5', 'esc_monthly',
            'ref_count', 'ref_cumul', 'ref_interets', 'ref_by_statut', 'ref_avg_taux', 'ref_duree', 'ref_monthly',
            'cumulGlobal', 'encoursRestant', 'encoursRestantGlobal',
            'pourcentageUtilisation', 'pourcentageUtilisationGlobal',
            'log_total', 'log_by_action', 'log_by_severity',
            'log_last24h', 'log_last7d', 'log_last5', 'log_daily'
        ));
    }

    private function monthlyTotals(string $model, string $amountField, string $dateField): array
    {
        $months = [];
        $frMonths = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $label = $frMonths[$date->month - 1] . ' ' . $date->format('y');
            $sum = (float) $model::whereYear($dateField, $date->year)
                ->whereMonth($dateField, $date->month)
                ->sum($amountField);
            $months[] = ['label' => $label, 'total' => $sum];
        }
        return $months;
    }
}
