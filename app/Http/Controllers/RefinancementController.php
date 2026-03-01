<?php

namespace App\Http\Controllers;

use App\Models\Refinancement;
use App\Http\Requests\RefinancementRequest;
use App\Services\AuditLogger;
use App\Exports\RefinancementsExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RefinancementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Refinancement::query();

        if ($request->filled('recherche')) {
            $query->where('libelle', 'LIKE', '%' . $request->recherche . '%');
        }
        if ($request->filled('dateDebut')) {
            $query->where('date_refinancement', '>=', $request->dateDebut);
        }
        if ($request->filled('dateFin')) {
            $query->where('date_refinancement', '<=', $request->dateFin);
        }
        if ($request->filled('montantMin')) {
            $query->where('montant_refinance', '>=', (float) $request->montantMin);
        }
        if ($request->filled('montantMax')) {
            $query->where('montant_refinance', '<=', (float) $request->montantMax);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $sortField = $request->input('sortField', 'ordre_saisie');
        $sortDirection = $request->input('sortDirection', 'asc');
        $allowedSortFields = ['ordre_saisie', 'libelle', 'montant_refinance', 'taux_interet', 'duree_en_mois', 'date_refinancement', 'encours_refinance', 'statut', 'total_interets', 'created_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $limit = (int) $request->input('limit', 10);
        $page = (int) $request->input('page', 1);
        $paginated = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'page' => $paginated->currentPage(),
            'limit' => $paginated->perPage(),
            'totalPages' => $paginated->lastPage(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $refinancement = Refinancement::findOrFail($id);
        return response()->json($refinancement);
    }

    public function store(RefinancementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['ordre_saisie'] = Refinancement::count() + 1;
        // total_interets will be auto-calculated by the model boot method

        $refinancement = Refinancement::create($data);

        AuditLogger::log(
            'CREATE',
            'refinancement',
            'info',
            'Création d\'un refinancement',
            'Refinancement "' . $refinancement->libelle . '" créé avec un montant de ' . number_format($refinancement->montant_refinance, 2, ',', ' ') . ' DH',
            'refinancement',
            $refinancement->id,
            ['after' => $refinancement->toArray()]
        );

        return response()->json($refinancement, 201);
    }

    public function update(RefinancementRequest $request, string $id): JsonResponse
    {
        $refinancement = Refinancement::findOrFail($id);
        $before = $refinancement->toArray();

        $refinancement->update($request->validated());
        $refinancement->refresh();

        AuditLogger::log(
            'UPDATE',
            'refinancement',
            'info',
            'Modification d\'un refinancement',
            'Refinancement "' . $refinancement->libelle . '" mis à jour',
            'refinancement',
            $refinancement->id,
            ['before' => $before, 'after' => $refinancement->toArray()]
        );

        return response()->json($refinancement);
    }

    public function destroy(string $id): JsonResponse
    {
        $refinancement = Refinancement::findOrFail($id);
        $data = $refinancement->toArray();

        $refinancement->delete();

        AuditLogger::log(
            'DELETE',
            'refinancement',
            'MEDIUM',
            'Suppression d\'un refinancement',
            'Refinancement "' . $data['libelle'] . '" supprimé',
            'refinancement',
            $id,
            ['before' => $data]
        );

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        $date = now()->format('Y-m-d');

        $query = Refinancement::query();
        if ($request->filled('recherche')) {
            $query->where('libelle', 'LIKE', '%' . $request->recherche . '%');
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        $refinancements = $query->orderBy('ordre_saisie')->get();

        AuditLogger::log(
            'EXPORT',
            'data',
            'info',
            'Export des refinancements',
            'Export ' . strtoupper($format) . ' des refinancements effectué (' . $refinancements->count() . ' enregistrements)',
            'refinancement',
            null,
            null,
            ['format' => $format, 'count' => $refinancements->count()]
        );

        if ($format === 'xlsx') {
            $filename = "refinancements_{$date}.xlsx";
            return Excel::download(new RefinancementsExport($refinancements), $filename);
        }

        // CSV with BOM
        $filename = "refinancements_{$date}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($refinancements) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['ID', 'Libellé', 'Montant Refinancé', 'Taux Intérêt (%)', 'Durée (mois)', 'Date Refinancement', 'Encours Refinancé', 'Statut', 'Total Intérêts', 'Date Création']);
            foreach ($refinancements as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->libelle,
                    $r->montant_refinance,
                    $r->taux_interet,
                    $r->duree_en_mois,
                    $r->date_refinancement?->format('Y-m-d'),
                    $r->encours_refinance,
                    $r->statut,
                    $r->total_interets,
                    $r->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
