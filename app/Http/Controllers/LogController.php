<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Http\Requests\LogRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Log::query();

        // Text search across message, description, entity_type, entity_id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('entity_type', 'LIKE', "%{$search}%")
                    ->orWhere('entity_id', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('entityType')) {
            $query->where('entity_type', $request->entityType);
        }
        if ($request->filled('dateStart')) {
            $query->where('timestamp', '>=', $request->dateStart);
        }
        if ($request->filled('dateEnd')) {
            $query->where('timestamp', '<=', Carbon::parse($request->dateEnd)->endOfDay());
        }

        // Sort by timestamp DESC by default
        $sortField = $request->input('sortField', 'timestamp');
        $sortDirection = $request->input('sortDirection', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $limit = (int)$request->input('limit', 50);
        $page = (int)$request->input('page', 1);
        $paginated = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'page' => $paginated->currentPage(),
            'limit' => $paginated->perPage(),
            'totalPages' => $paginated->lastPage(),
        ]);
    }

    public function store(LogRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['id'] = 'log_' . now()->format('YmdHis') . '_' . Str::random(5);
        $data['timestamp'] = now();

        if (!isset($data['severity'])) {
            $data['severity'] = 'info';
        }
        if (!isset($data['metadata'])) {
            $data['metadata'] = [];
        }

        $log = Log::create($data);

        // Enforce 10,000 entry cap
        $count = Log::count();
        if ($count > 10000) {
            $excess = $count - 10000;
            Log::orderBy('timestamp', 'asc')->limit($excess)->delete();
        }

        return response()->json($log, 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $log = Log::findOrFail($id);
        $log->delete();
        return response()->json(['success' => true]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        if ($request->query('confirm') !== 'true') {
            return response()->json([
                'error' => 'Confirmation requise. Ajoutez ?confirm=true pour supprimer tous les logs.'
            ], 400);
        }

        Log::truncate();
        return response()->json(['success' => true]);
    }

    public function stats(): JsonResponse
    {
        $logs = Log::all();

        $byCategory = $logs->groupBy('category')->map->count();
        $byAction = $logs->groupBy('action')->map->count();
        $bySeverity = $logs->groupBy('severity')->map->count();
        $byEntityType = $logs->groupBy('entity_type')->map->count();

        $now = now();
        $last24h = $logs->filter(fn($log) => Carbon::parse($log->timestamp)->isAfter($now->copy()->subDay()))->count();
        $last7d = $logs->filter(fn($log) => Carbon::parse($log->timestamp)->isAfter($now->copy()->subDays(7)))->count();

        return response()->json([
            'total' => $logs->count(),
            'byCategory' => $byCategory,
            'byAction' => $byAction,
            'bySeverity' => $bySeverity,
            'byEntityType' => $byEntityType,
            'last24Hours' => $last24h,
            'last7Days' => $last7d,
        ]);
    }

    public function export()
    {
        $logs = Log::orderByDesc('timestamp')->get();
        $date = now()->format('Y-m-d');
        $filename = "logs_{$date}.csv";

        \App\Services\AuditLogger::log(
            'EXPORT',
            'data',
            'LOW',
            'Export des logs',
            'Export CSV des logs effectué (' . $logs->count() . ' enregistrements)',
            'log',
            null,
            null,
        ['format' => 'csv', 'count' => $logs->count()]
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['ID', 'Date', 'Action', 'Catégorie', 'Sévérité', 'Message', 'Description', 'Utilisateur', 'Type Entité', 'ID Entité']);
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->timestamp,
                    $log->action,
                    $log->category,
                    $log->severity,
                    $log->message,
                    $log->description,
                    $log->user_id,
                    $log->entity_type,
                    $log->entity_id,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
