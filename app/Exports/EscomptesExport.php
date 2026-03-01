<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class EscomptesExport implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $escomptes;

    public function __construct(Collection $escomptes)
    {
        $this->escomptes = $escomptes;
    }

    public function collection(): Collection
    {
        return $this->escomptes->map(function ($e) {
            return [
                'ID' => $e->id,
                'Date de remise' => $e->date_remise?->format('Y-m-d'),
                'Libellé' => $e->libelle,
                'Montant' => $e->montant,
                'Ordre de saisie' => $e->ordre_saisie,
                'Date de création' => $e->created_at?->format('Y-m-d H:i:s'),
                'Date de modification' => $e->updated_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Date de remise', 'Libellé', 'Montant', 'Ordre de saisie', 'Date de création', 'Date de modification'];
    }

    public function title(): string
    {
        return 'Escomptes';
    }
}
