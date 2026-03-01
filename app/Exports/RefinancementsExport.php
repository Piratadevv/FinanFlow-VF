<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RefinancementsExport implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $refinancements;

    public function __construct(Collection $refinancements)
    {
        $this->refinancements = $refinancements;
    }

    public function collection(): Collection
    {
        return $this->refinancements->map(function ($r) {
            return [
                'ID' => $r->id,
                'Libellé' => $r->libelle,
                'Montant Refinancé' => $r->montant_refinance,
                'Taux Intérêt (%)' => $r->taux_interet,
                'Durée (mois)' => $r->duree_en_mois,
                'Date Refinancement' => $r->date_refinancement?->format('Y-m-d'),
                'Encours Refinancé' => $r->encours_refinance,
                'Statut' => $r->statut,
                'Total Intérêts' => $r->total_interets,
                'Date Création' => $r->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Libellé', 'Montant Refinancé', 'Taux Intérêt (%)', 'Durée (mois)', 'Date Refinancement', 'Encours Refinancé', 'Statut', 'Total Intérêts', 'Date Création'];
    }

    public function title(): string
    {
        return 'Refinancements';
    }
}
