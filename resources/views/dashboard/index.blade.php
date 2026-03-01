@extends('layouts.app')
@section('content')
<style>
    .dash-grid-4 { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    .dash-grid-2 { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    .dash-grid-3 { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    .dash-grid-3-inner { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
    .dash-grid-1-2 { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    @media (min-width: 640px) {
        .dash-grid-4 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width: 768px) {
        .dash-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .dash-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .dash-grid-1-2 { grid-template-columns: 1fr 2fr; }
    }
    @media (min-width: 1024px) {
        .dash-grid-4 { grid-template-columns: repeat(4, 1fr); }
    }
    /* Professional KPI card accents */
    .kpi-card { position: relative; overflow: hidden; transition: box-shadow 0.2s, transform 0.15s; }
    .kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); transform: translateY(-1px); }
    .kpi-card .kpi-accent { position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .kpi-accent-indigo { background: linear-gradient(90deg, #4F46E5, #6366F1); }
    .kpi-accent-teal   { background: linear-gradient(90deg, #0D9488, #14B8A6); }
    .kpi-accent-amber  { background: linear-gradient(90deg, #D97706, #F59E0B); }
    .kpi-accent-slate  { background: linear-gradient(90deg, #475569, #64748B); }
    /* Chart card subtle top border */
    .chart-card { border-top: 2px solid #E2E8F0; }
    /* Severity bar track */
    .sev-bar-track { background: #F1F5F9; height: 6px; border-radius: 3px; }
    .sev-bar-fill  { height: 6px; border-radius: 3px; transition: width 0.5s ease; }
</style>

<div x-data="dashboardPage()" x-init="init()">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold" style="color: #1E293B;">Tableau de Bord</h2>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full" style="background: #F0FDF4; border: 1px solid #BBF7D0;">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background: #4ADE80;"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2" style="background: #16A34A;"></span>
                </span>
                <span class="text-xs font-medium" style="color: #15803D;">En direct</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs" style="color: #94A3B8;" x-text="'Dernière mise à jour : ' + lastUpdated"></span>
            <button @click="refreshKpi()"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition"
                style="background: #F8FAFC; border: 1px solid #E2E8F0; color: #475569;"
                onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#F8FAFC'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Actualiser
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 1 — Global KPI Cards                       --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="dash-grid-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm kpi-card" style="border: 1px solid #E2E8F0;">
            <div class="kpi-accent kpi-accent-slate"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider" style="color: #64748B;">Autorisation Bancaire</span>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: #F1F5F9;">
                    <svg class="w-4.5 h-4.5" style="color: #475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold" style="color: #0F172A;" x-text="formatMontant(kpi.autorisationBancaire)"></p>
            <p class="text-xs mt-1" style="color: #94A3B8;">Limite de crédit configurée</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm kpi-card" style="border: 1px solid #E2E8F0;">
            <div class="kpi-accent kpi-accent-indigo"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider" style="color: #64748B;">Cumul Global</span>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: #EEF2FF;">
                    <svg class="w-4.5 h-4.5" style="color: #4F46E5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold" style="color: #0F172A;" x-text="formatMontant(kpi.cumulGlobal)"></p>
            <p class="text-xs mt-1" style="color: #94A3B8;">Escomptes + Refinancements</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm kpi-card" style="border: 1px solid #E2E8F0;">
            <div class="kpi-accent kpi-accent-teal"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider" style="color: #64748B;">Encours Restant</span>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: #F0FDFA;">
                    <svg class="w-4.5 h-4.5" style="color: #0D9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold" :style="kpi.encoursRestantGlobal >= 0 ? 'color:#0D9488' : 'color:#DC2626'" x-text="formatMontant(kpi.encoursRestantGlobal)"></p>
            <p class="text-xs mt-1" style="color: #94A3B8;">Marge disponible</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm kpi-card" style="border: 1px solid #E2E8F0;">
            <div class="kpi-accent kpi-accent-amber"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider" style="color: #64748B;">Taux d'Utilisation</span>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: #FFFBEB;">
                    <svg class="w-4.5 h-4.5" style="color: #D97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold" style="color: #0F172A;"><span x-text="kpi.pourcentageUtilisationGlobal"></span> %</p>
            <div class="mt-2 w-full rounded-full h-1.5" style="background: #E2E8F0;">
                <div class="h-1.5 rounded-full transition-all duration-700"
                     :style="'width:' + Math.min(kpi.pourcentageUtilisationGlobal, 100) + '%; background:' + (kpi.pourcentageUtilisationGlobal > 90 ? '#DC2626' : kpi.pourcentageUtilisationGlobal > 70 ? '#D97706' : '#0D9488')"></div>
            </div>
            <p class="text-xs mt-1" style="color: #94A3B8;">De l'autorisation bancaire</p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 2 — Escomptes vs Refinancements            --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="dash-grid-2 mb-6">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 1px solid #E2E8F0;">
            <div class="px-5 py-3 flex items-center justify-between" style="background: linear-gradient(135deg, #1E3A5F, #2563EB);">
                <h3 class="font-semibold text-sm" style="color: #fff;">Escomptes</h3>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $esc_count }}</span>
            </div>
            <div class="p-5">
                <div class="dash-grid-3-inner mb-4">
                    <div class="rounded-lg p-3 text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                        <p class="text-xs font-medium mb-1" style="color: #64748B;">Cumul Total</p>
                        <p class="text-sm font-bold" style="color: #1E293B;">{{ number_format($esc_cumul, 2, ',', ' ') }} DH</p>
                    </div>
                    <div class="rounded-lg p-3 text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                        <p class="text-xs font-medium mb-1" style="color: #64748B;">Actifs</p>
                        <p class="text-sm font-bold" style="color: #1E293B;">{{ $esc_by_statut['ACTIF'] }}</p>
                    </div>
                    <div class="rounded-lg p-3 text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                        <p class="text-xs font-medium mb-1" style="color: #64748B;">Montant Moyen</p>
                        <p class="text-sm font-bold" style="color: #1E293B;">{{ number_format($esc_avg, 2, ',', ' ') }} DH</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs" style="color: #64748B;">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #0D9488;"></span>ACTIF : {{ $esc_by_statut['ACTIF'] }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #94A3B8;"></span>TERMINÉ : {{ $esc_by_statut['TERMINE'] }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #D97706;"></span>SUSPENDU : {{ $esc_by_statut['SUSPENDU'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 1px solid #E2E8F0;">
            <div class="px-5 py-3 flex items-center justify-between" style="background: linear-gradient(135deg, #3B1F6E, #7C3AED);">
                <h3 class="font-semibold text-sm" style="color: #fff;">Refinancements</h3>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $ref_count }}</span>
            </div>
            <div class="p-5">
                <div class="dash-grid-3-inner mb-4">
                    <div class="rounded-lg p-3 text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                        <p class="text-xs font-medium mb-1" style="color: #64748B;">Cumul Refinancé</p>
                        <p class="text-sm font-bold" style="color: #1E293B;">{{ number_format($ref_cumul, 2, ',', ' ') }} DH</p>
                    </div>
                    <div class="rounded-lg p-3 text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                        <p class="text-xs font-medium mb-1" style="color: #64748B;">Total Intérêts</p>
                        <p class="text-sm font-bold" style="color: #1E293B;">{{ number_format($ref_interets, 2, ',', ' ') }} DH</p>
                    </div>
                    <div class="rounded-lg p-3 text-center" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                        <p class="text-xs font-medium mb-1" style="color: #64748B;">Taux Moyen</p>
                        <p class="text-sm font-bold" style="color: #1E293B;">{{ $ref_avg_taux }} %</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs" style="color: #64748B;">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #0D9488;"></span>ACTIF : {{ $ref_by_statut['ACTIF'] }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #94A3B8;"></span>TERMINÉ : {{ $ref_by_statut['TERMINE'] }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #D97706;"></span>SUSPENDU : {{ $ref_by_statut['SUSPENDU'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 3 — Monthly Evolution Chart                --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 chart-card" style="border: 1px solid #E2E8F0;">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold" style="color: #1E293B;">Évolution Mensuelle des 6 Derniers Mois</h3>
            <div class="flex items-center gap-1 rounded-lg p-0.5" style="background: #F1F5F9;">
                <button @click="monthlyFilter = 'all'; updateMonthlyChart()"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition"
                    :style="monthlyFilter === 'all' ? 'background:#fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); color:#1E293B;' : 'color:#64748B;'">Tout</button>
                <button @click="monthlyFilter = 'esc'; updateMonthlyChart()"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition"
                    :style="monthlyFilter === 'esc' ? 'background:#fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); color:#2563EB;' : 'color:#64748B;'">Escomptes</button>
                <button @click="monthlyFilter = 'ref'; updateMonthlyChart()"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition"
                    :style="monthlyFilter === 'ref' ? 'background:#fff; box-shadow: 0 1px 3px rgba(0,0,0,0.08); color:#7C3AED;' : 'color:#64748B;'">Refinancements</button>
            </div>
        </div>
        <div class="relative" style="height:280px">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 4 — Four Charts 2×2 Grid                   --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="dash-grid-2 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 chart-card" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-3" style="color: #1E293B;">Répartition des Escomptes par Statut</h3>
            <div class="relative" style="height:240px">
                @if($esc_count > 0)
                    <canvas id="escStatutChart"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-full" style="color: #94A3B8;">
                        <p class="text-sm">Aucune donnée disponible</p>
                        <a href="{{ route('escomptes.index') }}" class="mt-2 text-xs hover:underline" style="color: #4F46E5;">Ajouter un escompte →</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 chart-card" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-3" style="color: #1E293B;">Répartition des Refinancements par Statut</h3>
            <div class="relative" style="height:240px">
                @if($ref_count > 0)
                    <canvas id="refStatutChart"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-full" style="color: #94A3B8;">
                        <p class="text-sm">Aucune donnée disponible</p>
                        <a href="{{ route('refinancements.index') }}" class="mt-2 text-xs hover:underline" style="color: #4F46E5;">Ajouter un refinancement →</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 chart-card" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-3" style="color: #1E293B;">Activité des Logs par Action</h3>
            <div class="relative" style="height:240px">
                @if($log_total > 0)
                    <canvas id="logActionChart"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-full" style="color: #94A3B8;">
                        <p class="text-sm">Aucune donnée disponible</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 chart-card" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-3" style="color: #1E293B;">Activité des 7 Derniers Jours</h3>
            <div class="relative" style="height:240px">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 5 — Three Widgets                          --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="dash-grid-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-3" style="color: #1E293B;">Jauge d'Utilisation</h3>
            <div class="relative" style="height:180px">
                <canvas id="gaugeChart"></canvas>
            </div>
            <div class="text-center mt-2">
                <p class="text-3xl font-bold" :style="kpi.pourcentageUtilisation > 90 ? 'color:#DC2626' : kpi.pourcentageUtilisation > 70 ? 'color:#D97706' : 'color:#0D9488'" x-text="kpi.pourcentageUtilisation + ' %'"></p>
                <p class="text-xs mt-1" style="color: #94A3B8;">{{ number_format($esc_cumul, 0, ',', ' ') }} DH utilisés sur {{ number_format($autorisation, 0, ',', ' ') }} DH</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-3" style="color: #1E293B;">Durée des Refinancements</h3>
            <div class="relative" style="height:220px">
                @if($ref_count > 0)
                    <canvas id="durationChart"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-full" style="color: #94A3B8;">
                        <p class="text-sm">Aucune donnée disponible</p>
                        <a href="{{ route('refinancements.index') }}" class="mt-2 text-xs hover:underline" style="color: #4F46E5;">Ajouter un refinancement →</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-3" style="color: #1E293B;">Top 5 Escomptes</h3>
            @if(count($esc_top5) > 0)
                <div class="space-y-2">
                    @foreach($esc_top5 as $i => $top)
                        <div class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg" style="{{ $i === 0 ? 'background: #FFFBEB; border: 1px solid #FDE68A;' : 'background: #F8FAFC; border: 1px solid #E2E8F0;' }}">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" style="{{ $i === 0 ? 'background: #D97706; color: #fff;' : 'background: #E2E8F0; color: #475569;' }}">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate" style="color: #1E293B;">{{ $top['libelle'] }}</p>
                            </div>
                            <span class="text-sm font-bold whitespace-nowrap" style="color: #334155;">{{ number_format($top['montant'], 2, ',', ' ') }} DH</span>
                            @if($esc_max > 0)
                            <div class="absolute bottom-0 left-0 h-0.5 rounded-full opacity-30" style="background: #4F46E5; width: {{ round(($top['montant'] / $esc_max) * 100) }}%"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-48" style="color: #94A3B8;">
                    <p class="text-sm">Aucune donnée disponible</p>
                    <a href="{{ route('escomptes.index') }}" class="mt-2 text-xs hover:underline" style="color: #4F46E5;">Ajouter un escompte →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 6 — Severity + Activity Feed               --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="dash-grid-1-2 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-4" style="color: #1E293B;">Sévérité des Logs</h3>
            <div class="space-y-3">
                @php
                    $sevStyles = [
                        'CRITICAL' => '#DC2626',
                        'HIGH'     => '#D97706',
                        'MEDIUM'   => '#CA8A04',
                        'LOW'      => '#2563EB',
                        'info'     => '#94A3B8',
                    ];
                @endphp
                @foreach($log_by_severity as $sev => $cnt)
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full" style="background: {{ $sevStyles[$sev] }};"></span>
                        <span class="text-xs font-medium w-16" style="color: #475569;">{{ $sev }}</span>
                        <div class="flex-1 sev-bar-track">
                            <div class="sev-bar-fill" style="background: {{ $sevStyles[$sev] }}; width: {{ $log_total > 0 ? round(($cnt / $log_total) * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs font-bold w-8 text-right" style="color: #334155;">{{ $cnt }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3" style="border-top: 1px solid #F1F5F9;">
                <div class="flex items-center justify-between text-xs" style="color: #64748B;">
                    <span>Total des logs</span><span class="font-bold" style="color: #334155;">{{ $log_total }}</span>
                </div>
                <div class="flex items-center justify-between text-xs mt-2" style="color: #64748B;">
                    <span>Dernières 24h</span><span class="font-bold" style="color: #334155;">{{ $log_last24h }}</span>
                </div>
                <div class="flex items-center justify-between text-xs mt-1" style="color: #64748B;">
                    <span>7 derniers jours</span><span class="font-bold" style="color: #334155;">{{ $log_last7d }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5" style="border: 1px solid #E2E8F0;">
            <h3 class="text-sm font-semibold mb-4" style="color: #1E293B;">Activité Récente</h3>
            @if(count($log_last5) > 0)
                <div class="space-y-3">
                    @foreach($log_last5 as $log)
                        @php
                            $borderStyle = match($log['severity'] ?? 'info') {
                                'CRITICAL' => 'border-left: 3px solid #DC2626;',
                                'HIGH'     => 'border-left: 3px solid #D97706;',
                                'MEDIUM'   => 'border-left: 3px solid #CA8A04;',
                                'LOW'      => 'border-left: 3px solid #2563EB;',
                                default    => 'border-left: 3px solid #CBD5E1;',
                            };
                            $actionStyle = match($log['action'] ?? '') {
                                'CREATE' => 'background: #F0FDF4; color: #166534;',
                                'UPDATE' => 'background: #EFF6FF; color: #1E40AF;',
                                'DELETE' => 'background: #FEF2F2; color: #991B1B;',
                                'LOGIN'  => 'background: #F5F3FF; color: #5B21B6;',
                                'EXPORT' => 'background: #F0FDFA; color: #115E59;',
                                default  => 'background: #F8FAFC; color: #475569;',
                            };
                        @endphp
                        <div class="pl-4 py-2 rounded-r-lg" style="{{ $borderStyle }} background: #FAFBFC;">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded" style="{{ $actionStyle }}">{{ $log['action'] ?? '—' }}</span>
                                <span class="text-sm font-medium" style="color: #1E293B;">{{ $log['message'] ?? $log['description'] ?? '' }}</span>
                                <span class="ml-auto text-xs" style="color: #94A3B8;" title="{{ $log['timestamp'] ?? '' }}">
                                    @if(!empty($log['timestamp']))
                                        {{ \Carbon\Carbon::parse($log['timestamp'])->locale('fr')->diffForHumans() }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-xs" style="color: #64748B;">
                              
                                @if(!empty($log['entity_type']))
                                    <span class="px-1.5 py-0.5 rounded" style="background: #F1F5F9; color: #475569;">{{ $log['entity_type'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('logs.index') }}" class="inline-flex items-center gap-1 mt-4 text-sm font-medium" style="color: #4F46E5;"
                   onmouseover="this.style.color='#3730A3'" onmouseout="this.style.color='#4F46E5'">
                    Voir tous les logs
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            @else
                <div class="flex items-center justify-center h-48 text-sm" style="color: #94A3B8;">Aucune activité récente</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function dashboardPage() {
    // Professional color palette
    const C = {
        indigo: '#4F46E5', indigoLight: 'rgba(79,70,229,0.8)', indigoSoft: 'rgba(79,70,229,0.08)',
        violet: '#7C3AED', violetLight: 'rgba(124,58,237,0.8)', violetSoft: 'rgba(124,58,237,0.08)',
        teal:   '#0D9488', tealLight: 'rgba(13,148,136,0.8)',
        slate:  '#64748B', slateDark: '#334155',
        amber:  '#D97706',
        rose:   '#E11D48',
        emerald:'#059669',
        sky:    '#0284C7',
        neutral:'#F1F5F9',
        border: '#E2E8F0',
        gridLine: 'rgba(226,232,240,0.6)',
    };
    return {
        lastUpdated: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
        monthlyFilter: 'all',
        charts: {},

        kpi: {
            autorisationBancaire: {{ $autorisation }},
            cumulGlobal: {{ $cumulGlobal }},
            encoursRestantGlobal: {{ $encoursRestantGlobal }},
            pourcentageUtilisationGlobal: {{ $pourcentageUtilisationGlobal }},
            pourcentageUtilisation: {{ $pourcentageUtilisation }},
            cumulTotal: {{ $esc_cumul }},
            encoursRestant: {{ $encoursRestant }},
        },

        escMonthly: @json($esc_monthly),
        refMonthly: @json($ref_monthly),
        escStatuts: @json($esc_by_statut),
        refStatuts: @json($ref_by_statut),
        logActions: @json($log_by_action),
        logDaily: @json($log_daily),
        refDuree: @json($ref_duree),

        formatMontant(v) {
            if (v === null || v === undefined) return '0,00 DH';
            return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v) + ' DH';
        },

        async init() {
            await this.$nextTick();
            setTimeout(() => this.initCharts(), 150);
            this._interval = setInterval(() => this.refreshKpi(), 30000);
        },

        async refreshKpi() {
            try {
                const res = await fetch('/api/dashboard/kpi', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                if (res.ok) {
                    const d = await res.json();
                    this.kpi = {
                        autorisationBancaire: d.autorisationBancaire,
                        cumulGlobal: d.cumulGlobal,
                        encoursRestantGlobal: d.encoursRestantGlobal,
                        pourcentageUtilisationGlobal: d.pourcentageUtilisationGlobal,
                        pourcentageUtilisation: d.pourcentageUtilisation,
                        cumulTotal: d.cumulTotal,
                        encoursRestant: d.encoursRestant,
                    };
                    this.lastUpdated = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
                    if (this.charts.gauge) {
                        this.charts.gauge.data.datasets[0].data = [d.pourcentageUtilisation, 100 - d.pourcentageUtilisation];
                        const c = d.pourcentageUtilisation > 90 ? '#DC2626' : d.pourcentageUtilisation > 70 ? C.amber : C.teal;
                        this.charts.gauge.data.datasets[0].backgroundColor = [c, C.neutral];
                        this.charts.gauge.update();
                    }
                }
            } catch (e) {}
        },

        initCharts() {
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.font.size = 11;
            Chart.defaults.color = C.slate;
            Chart.defaults.plugins.legend.position = 'bottom';
            Chart.defaults.plugins.legend.labels.padding = 16;
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.pointStyleWidth = 8;
            this.initMonthlyChart();
            this.initEscStatutChart();
            this.initRefStatutChart();
            this.initLogActionChart();
            this.initWeeklyChart();
            this.initGaugeChart();
            this.initDurationChart();
        },

        initMonthlyChart() {
            const ctx = document.getElementById('monthlyChart');
            if (!ctx) return;
            this.charts.monthly = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.escMonthly.map(m => m.label),
                    datasets: [
                        { label: 'Escomptes', data: this.escMonthly.map(m => m.total), backgroundColor: C.indigoLight, borderRadius: 4, barPercentage: 0.5, categoryPercentage: 0.7 },
                        { label: 'Refinancements', data: this.refMonthly.map(m => m.total), backgroundColor: C.violetLight, borderRadius: 4, barPercentage: 0.5, categoryPercentage: 0.7 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { tooltip: { backgroundColor: '#1E293B', titleColor: '#F8FAFC', bodyColor: '#CBD5E1', borderColor: C.border, borderWidth: 1, cornerRadius: 8, padding: 12,
                        callbacks: { label: c => c.dataset.label + ' : ' + new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2 }).format(c.parsed.y) + ' DH' } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: C.gridLine }, ticks: { callback: v => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) + ' DH' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        },

        updateMonthlyChart() {
            if (!this.charts.monthly) return;
            this.charts.monthly.data.datasets[0].hidden = this.monthlyFilter === 'ref';
            this.charts.monthly.data.datasets[1].hidden = this.monthlyFilter === 'esc';
            this.charts.monthly.update();
        },

        initEscStatutChart() {
            const ctx = document.getElementById('escStatutChart');
            if (!ctx) return;
            this.charts.escStatut = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: ['Actif', 'Terminé', 'Suspendu'], datasets: [{ data: [this.escStatuts.ACTIF, this.escStatuts.TERMINE, this.escStatuts.SUSPENDU], backgroundColor: [C.teal, '#94A3B8', C.amber], borderWidth: 0, cutout: '68%', spacing: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { backgroundColor: '#1E293B', cornerRadius: 8, padding: 10 } } }
            });
        },

        initRefStatutChart() {
            const ctx = document.getElementById('refStatutChart');
            if (!ctx) return;
            this.charts.refStatut = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: ['Actif', 'Terminé', 'Suspendu'], datasets: [{ data: [this.refStatuts.ACTIF, this.refStatuts.TERMINE, this.refStatuts.SUSPENDU], backgroundColor: [C.violet, '#94A3B8', C.amber], borderWidth: 0, cutout: '68%', spacing: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { backgroundColor: '#1E293B', cornerRadius: 8, padding: 10 } } }
            });
        },

        initLogActionChart() {
            const ctx = document.getElementById('logActionChart');
            if (!ctx) return;
            const actionLabels = { CREATE: 'Création', UPDATE: 'Modification', DELETE: 'Suppression', LOGIN: 'Connexion', EXPORT: 'Export' };
            this.charts.logAction = new Chart(ctx, {
                type: 'bar',
                data: { labels: Object.keys(this.logActions).map(k => actionLabels[k] || k), datasets: [{ data: Object.values(this.logActions), backgroundColor: [C.emerald, C.sky, C.rose, C.violet, C.teal], borderRadius: 4, barPercentage: 0.5 }] },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1E293B', cornerRadius: 8, padding: 10 } }, scales: { x: { beginAtZero: true, grid: { color: C.gridLine }, ticks: { stepSize: 1 } }, y: { grid: { display: false } } } }
            });
        },

        initWeeklyChart() {
            const ctx = document.getElementById('weeklyChart');
            if (!ctx) return;
            this.charts.weekly = new Chart(ctx, {
                type: 'line',
                data: { labels: this.logDaily.map(d => d.label), datasets: [{ label: 'Activités', data: this.logDaily.map(d => d.count), borderColor: C.indigo, backgroundColor: C.indigoSoft, fill: true, tension: 0.4, pointBackgroundColor: '#fff', pointBorderColor: C.indigo, pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, borderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1E293B', cornerRadius: 8, padding: 10 } }, scales: { y: { beginAtZero: true, grid: { color: C.gridLine }, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
            });
        },

        initGaugeChart() {
            const ctx = document.getElementById('gaugeChart');
            if (!ctx) return;
            const pct = {{ $pourcentageUtilisation }};
            const c = pct > 90 ? '#DC2626' : pct > 70 ? C.amber : C.teal;
            this.charts.gauge = new Chart(ctx, {
                type: 'doughnut',
                data: { datasets: [{ data: [pct, 100 - pct], backgroundColor: [c, C.neutral], borderWidth: 0, cutout: '78%' }] },
                options: { responsive: true, maintainAspectRatio: false, rotation: -90, circumference: 180, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
            });
        },

        initDurationChart() {
            const ctx = document.getElementById('durationChart');
            if (!ctx) return;
            this.charts.duration = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: ['≤ 12 mois', '13–24 mois', '25–36 mois', '> 36 mois'], datasets: [{ data: [this.refDuree.lte12, this.refDuree['13_24'], this.refDuree['25_36'], this.refDuree.gt36], backgroundColor: [C.indigo, C.violet, C.rose, C.amber], borderWidth: 0, cutout: '58%', spacing: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { tooltip: { backgroundColor: '#1E293B', cornerRadius: 8, padding: 10 } } }
            });
        },
    }
}
</script>
@endpush
@endsection
