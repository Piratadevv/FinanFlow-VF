<?php

namespace App\Http\Controllers;

use App\Models\Escompte;
use App\Http\Requests\EscompteRequest;
use App\Services\AuditLogger;
use App\Exports\EscomptesExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EscompteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Escompte::query();

        // Text search on libelle
        if ($request->filled('recherche')) {
            $query->where('libelle', 'LIKE', '%' . $request->recherche . '%');
        }

        // Date range
        if ($request->filled('dateDebut')) {
            $query->where('date_remise', '>=', $request->dateDebut);
        }
        if ($request->filled('dateFin')) {
            $query->where('date_remise', '<=', $request->dateFin);
        }

        // Amount range
        if ($request->filled('montantMin')) {
            $query->where('montant', '>=', (float) $request->montantMin);
        }
        if ($request->filled('montantMax')) {
            $query->where('montant', '<=', (float) $request->montantMax);
        }

        // Sorting
        $sortField = $request->input('sortField', 'ordre_saisie');
        $sortDirection = $request->input('sortDirection', 'asc');
        $allowedSortFields = ['ordre_saisie', 'libelle', 'montant', 'date_remise', 'numero_effet', 'nom_tireur', 'statut', 'created_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
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
        $escompte = Escompte::findOrFail($id);
        return response()->json($escompte);
    }

    public function store(EscompteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['ordre_saisie'] = Escompte::count() + 1;

        $escompte = Escompte::create($data);

        AuditLogger::log(
            'CREATE',
            'escompte',
            'info',
            'Création d\'un nouvel escompte',
            'Escompte "' . $escompte->libelle . '" créé avec un montant de ' . number_format($escompte->montant, 2, ',', ' ') . ' DH',
            'escompte',
            $escompte->id,
            ['after' => $escompte->toArray()]
        );

        return response()->json($escompte, 201);
    }

    public function update(EscompteRequest $request, string $id): JsonResponse
    {
        $escompte = Escompte::findOrFail($id);
        $before = $escompte->toArray();

        $escompte->update($request->validated());
        $escompte->refresh();

        AuditLogger::log(
            'UPDATE',
            'escompte',
            'info',
            'Modification d\'un escompte',
            'Escompte "' . $escompte->libelle . '" mis à jour',
            'escompte',
            $escompte->id,
            ['before' => $before, 'after' => $escompte->toArray()]
        );

        return response()->json($escompte);
    }

    public function destroy(string $id): JsonResponse
    {
        $escompte = Escompte::findOrFail($id);
        $data = $escompte->toArray();

        $escompte->delete();

        AuditLogger::log(
            'DELETE',
            'escompte',
            'MEDIUM',
            'Suppression d\'un escompte',
            'Escompte "' . $data['libelle'] . '" supprimé',
            'escompte',
            $id,
            ['before' => $data]
        );

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        $date = now()->format('Y-m-d');

        // Build query with same filters as index
        $query = Escompte::query();
        if ($request->filled('recherche')) {
            $query->where('libelle', 'LIKE', '%' . $request->recherche . '%');
        }
        if ($request->filled('dateDebut')) {
            $query->where('date_remise', '>=', $request->dateDebut);
        }
        if ($request->filled('dateFin')) {
            $query->where('date_remise', '<=', $request->dateFin);
        }
        if ($request->filled('montantMin')) {
            $query->where('montant', '>=', (float) $request->montantMin);
        }
        if ($request->filled('montantMax')) {
            $query->where('montant', '<=', (float) $request->montantMax);
        }
        $escomptes = $query->orderBy('ordre_saisie')->get();

        AuditLogger::log(
            'EXPORT',
            'data',
            'info',
            'Export des escomptes',
            'Export ' . strtoupper($format) . ' des escomptes effectué (' . $escomptes->count() . ' enregistrements)',
            'escompte',
            null,
            null,
            ['format' => $format, 'count' => $escomptes->count()]
        );

        if ($format === 'xlsx') {
            $filename = "escomptes_{$date}.xlsx";
            return Excel::download(new EscomptesExport($escomptes), $filename);
        }

        // CSV with BOM
        $filename = "escomptes_{$date}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($escomptes) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM
            fwrite($file, "\xEF\xBB\xBF");
            // Header row
            fputcsv($file, ['ID', 'Date de remise', 'Libellé', 'Montant', 'Ordre de saisie', 'Date de création', 'Date de modification']);
            foreach ($escomptes as $e) {
                fputcsv($file, [
                    $e->id,
                    $e->date_remise?->format('Y-m-d'),
                    $e->libelle,
                    $e->montant,
                    $e->ordre_saisie,
                    $e->created_at?->format('Y-m-d H:i:s'),
                    $e->updated_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function recalculate(): JsonResponse
    {
        // Stub — as per spec
        return response()->json(['success' => true]);
    }
}
